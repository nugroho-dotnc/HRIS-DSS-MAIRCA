<?php

use App\Models\Application;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\RecruitmentCriteria;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => 'Sesi Interview DSS'])] class extends Component
{
    public int $sessionId;

    /** @var array<int, mixed> nilai skor per criteria_id */
    public array $scores = [];

    private ?\Illuminate\Database\Eloquent\Collection $cachedCriterias = null;
    private ?InterviewSession $cachedSession = null;

    public function mount(): void
    {
        $session = InterviewSession::with([
            'application.vacancy.position',
            'scores',
        ])->findOrFail($this->sessionId);

        // Pre-fill nilai yang sudah ada sebelumnya
        foreach ($session->scores as $score) {
            $this->scores[$score->criteria_id] = $score->score;
        }
    }

    public function session(): InterviewSession
    {
        if ($this->cachedSession === null) {
            $this->cachedSession = InterviewSession::with([
                'application.candidate',
                'application.vacancy.position.department',
                'scores',
            ])->findOrFail($this->sessionId);
        }
        return $this->cachedSession;
    }

    public function criterias(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->cachedCriterias === null) {
            $session = $this->session();
            $positionId = $session->application->vacancy->position->id ?? null;

            if (!$positionId) {
                return collect();
            }

            $this->cachedCriterias = RecruitmentCriteria::with('likertScales')
                ->where('position_id', $positionId)
                ->orderBy('name')
                ->get();
        }
        return $this->cachedCriterias;
    }

    /** Deteksi apakah kriteria ini adalah IPK berdasarkan nama. */
    private function isIpk(string $name): bool
    {
        return str_contains(strtolower($name), 'ipk');
    }

    // deteksi apakah kriteria ini adalah gaji berdasarkan nama
    private function isGaji(string $name): bool{
        return str_contains(strtolower($name), "gaji");
    }

    /** Build aturan validasi dinamis berdasarkan daftar kriteria. */
    private function buildRules(): array
    {
        $rules = [];
        foreach ($this->criterias() as $criteria) {
            $key = "scores.{$criteria->id}";
            if ($criteria->data_type === 'kuantitatif') {
                if($this->isGaji($criteria->name)){
                    $rules[$key] = "required|numeric|min:0";
                }else{
                    $max = $this->isIpk($criteria->name) ? 4 : 100;
                    $rules[$key] = "required|numeric|min:0|max:{$max}";
                }
            } else {
                $rules[$key] = 'required';
            }
        }
        return $rules;
    }

    /** Build pesan error dinamis berdasarkan daftar kriteria. */
    private function buildMessages(): array
    {
        $messages = [];
        foreach ($this->criterias() as $criteria) {
            $key  = "scores.{$criteria->id}";
            $name = $criteria->name;
            $isIpk = $this->isIpk($name);
            $isGaji = $this->isGaji($name);
            $max   = $isIpk ? '4.00' : '100';

            $messages["{$key}.required"] = "Kriteria '{$name}' belum diisi.";
            $messages["{$key}.numeric"]  = "'{$name}' harus berupa angka.";
            $messages["{$key}.min"]      = $isIpk
                ? "IPK tidak boleh kurang dari 0.00."
                : "'{$name}' tidak boleh kurang dari 0.";
            if (!$isGaji) {
                $messages["{$key}.max"]      = $isIpk
                    ? "IPK tidak boleh melebihi 4.00."
                    : "'{$name}' tidak boleh melebihi {$max}.";
            }
            
        }
        return $messages;
    }

    /**
     * Dipanggil otomatis Livewire tiap kali $scores berubah.
     * Validasi field tunggal secara real-time (saat blur).
     */
    public function updatedScores(mixed $value, string $key): void
    {
        $rules    = $this->buildRules();
        $messages = $this->buildMessages();
        $field    = "scores.{$key}";

        if (isset($rules[$field])) {
            $this->validateOnly($field, $rules, $messages);
        }
    }

    public function save(): void
    {
        // Validasi semua field sekaligus — error muncul inline via flux:error
        $this->validate($this->buildRules(), $this->buildMessages());

        $session = InterviewSession::findOrFail($this->sessionId);

        // Upsert semua nilai ke interview_scores
        foreach ($this->criterias() as $criteria) {
            InterviewScore::updateOrCreate(
                [
                    'session_id'  => $this->sessionId,
                    'criteria_id' => $criteria->id,
                ],
                [
                    'score' => $this->scores[$criteria->id],
                ]
            );
        }

        // Ubah status aplikasi ke interview_done jika belum
        $application = Application::findOrFail($session->application_id);
        if ($application->status !== 'interview_done') {
            $application->status = 'interview_done';
            $application->save();
        }

        Flux::toast('Nilai berhasil disimpan! Status lamaran diperbarui ke Interview Selesai.');
    }
};
?>

<div class="flex flex-1 flex-col gap-6">
    <livewire:bread-crumbs/>

    @php
        $session  = $this->session();
        $app      = $session->application;
        $criterias = $this->criterias();
    @endphp

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Pengisian Nilai DSS</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                Kandidat: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $app->candidate->name }}</span>
                &mdash; Posisi: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $app->vacancy->position->position_name ?? '-' }}</span>
            </p>
        </div>
        <flux:button icon="arrow-left" href="{{ route('hr.interviews') }}" wire:navigate class="cursor-pointer">
            Kembali
        </flux:button>
    </div>

    <flux:separator/>

    {{-- Info Sesi --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
            <span class="text-xs text-zinc-400 uppercase tracking-wide">Kode Lamaran</span>
            <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $app->application_code }}</span>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
            <span class="text-xs text-zinc-400 uppercase tracking-wide">Departemen</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $app->vacancy->position->department->department_name ?? '-' }}</span>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
            <span class="text-xs text-zinc-400 uppercase tracking-wide">Jadwal Interview</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                {{ \Carbon\Carbon::parse($session->interview_date)->translatedFormat('d M Y, H:i') }} WIB
            </span>
        </div>
    </div>

    {{-- Form Kriteria --}}
    @if($criterias->isEmpty())
        <flux:callout inline align="center">
            <flux:callout.heading icon="exclamation-triangle" class="mx-auto">Tidak ada kriteria</flux:callout.heading>
            <flux:callout.text>
                Belum ada kriteria rekrutmen yang didefinisikan untuk posisi ini.
                Silakan tambahkan kriteria terlebih dahulu melalui menu Positions.
            </flux:callout.text>
        </flux:callout>
    @else
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-6 flex flex-col gap-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                <flux:icon name="clipboard-document-list" class="size-4"/> Kriteria Penilaian
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                @foreach($criterias as $criteria)
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $criteria->name }}
                            </span>
                            <flux:badge size="sm" color="{{ $criteria->type === 'benefit' ? 'green' : 'red' }}">
                                {{ ucfirst($criteria->type) }}
                            </flux:badge>
                            <flux:badge size="sm" color="zinc">
                                Bobot {{ $criteria->weight }}%
                            </flux:badge>
                        </div>

                        @if($criteria->data_type === 'kualitatif')
                            {{-- Dropdown Likert --}}
                            <flux:select
                                wire:model.live="scores.{{ $criteria->id }}"
                                placeholder="Pilih nilai..."
                                size="sm"
                            >
                                @foreach($criteria->likertScales->sortBy('value') as $scale)
                                    <flux:select.option value="{{ $scale->value }}">
                                        {{ $scale->label }} ({{ $scale->value }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="scores.{{ $criteria->id }}" />
                        @else
                            {{-- Input angka kuantitatif --}}
                            @php
                                $isIpk  = str_contains(strtolower($criteria->name), 'ipk');
                                $isGaji = str_contains(strtolower($criteria->name), 'gaji');
                                $maxVal = $isIpk ? 4 : ($isGaji ? null : 100);
                                $stepVal = $isIpk ? '0.01' : '1';
                                
                                if ($isIpk) {
                                    $placeholder = '0.00 – 4.00';
                                    $helpText = 'Rentang: 0.00 – 4.00';
                                } elseif ($isGaji) {
                                    $placeholder = 'Minimal 0';
                                    $helpText = 'Minimal 0 (tidak ada batas maksimum)';
                                } else {
                                    $placeholder = '0 – 100';
                                    $helpText = 'Rentang: 0 – 100';
                                }
                            @endphp
                            @if($maxVal !== null)
                                <flux:input
                                    wire:model.blur="scores.{{ $criteria->id }}"
                                    type="number"
                                    step="{{ $stepVal }}"
                                    min="0"
                                    max="{{ $maxVal }}"
                                    placeholder="{{ $placeholder }}"
                                    size="sm"
                                />
                            @else
                                <flux:input
                                    wire:model.blur="scores.{{ $criteria->id }}"
                                    type="number"
                                    step="{{ $stepVal }}"
                                    min="0"
                                    placeholder="{{ $placeholder }}"
                                    size="sm"
                                />
                            @endif
                            <flux:error name="scores.{{ $criteria->id }}" />
                            <span class="text-xs text-zinc-400">
                                {{ $helpText }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-2">
                <flux:button
                    icon="check"
                    variant="primary"
                    class="cursor-pointer"
                    wire:click="save"
                    wire:confirm="Simpan semua nilai dan tandai sesi interview ini sebagai selesai?"
                >
                    Simpan Nilai
                </flux:button>
            </div>
        </div>
    @endif
</div>
