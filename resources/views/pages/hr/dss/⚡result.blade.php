<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\MAIRCA;
use App\Services\NotificationService;
use App\Models\Application;
use App\Models\RecruitmentResult;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

new #[Layout('layouts::hr', [
    'page_title' => 'Hasil Perhitungan MAIRCA',
    'page_description' => 'Detail perangkingan kandidat pelamar berdasarkan metode DSS MAIRCA.'
])] class extends Component
{
    public int $vacancyId;

    public function result(): array|string
    {
        try {
            $service = new MAIRCA();
            $calculation = $service->calculate($this->vacancyId);

            // Trigger Notifikasi ke Candidate (Aman karena sudah ada cek mencegah duplikasi di Service)
            try {
                app(NotificationService::class)->notifyDssCompleted($this->vacancyId);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Notification] Gagal kirim notifikasi DSS completed (Web): ' . $e->getMessage());
            }

            return $calculation;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function decideHired(int $applicationId){
        DB::beginTransaction();
        try{
            $application = Application::with(["candidate", "vacancy.position.department", "result"])->findOrFail($applicationId);

            // update keputusan di tabel recruitment_results
            if($application->result){
                $application->result->decission = "hired";
                $application->result->save();
            }else{
                RecruitmentResult::create([
                    "application_id" => $applicationId,
                    "final_score" => 0.0,
                    "ranking" => 1,
                    "decission" => "hired",
                ]);
            }

            // update status lamaran ke "hired"
            $application->status = "hired";
            $application->save();

            // daftarkan candidate sebagai user dengan role "employee"
            $candidate = $application->candidate;
            $user = User::where("email", $candidate->email)->first();

            if($user){
                if($user->role === "candidate"){
                    $user->role = "employee";
                    $user->save();
                }
            }else{
                $user = User::create([
                    "name" => $candidate->name,
                    "email" => $candidate->email,
                    "password" => Hash::make("password"),
                    "role" => "employee",
                    "status" => "active"
                ]);
            }

            // masukkan data ke tabel employees dengan supervisor dari departemen terkait
            $deptId = $application->vacancy->position->department->id;
            
            // cari supervisor di departemen yang sama
            $defaultSupervisor = Employee::where("department_id", $deptId)->whereHas("user", function($query){
                $query->where("role", "supervisor");
            })->first();

            // fallback 1: kalo ga ada supervisor di departemen terkait, ambil id supervisor dari departemen lain
            if(!$defaultSupervisor){
                $defaultSupervisor = Employee::whereHas("user", function($query){
                    $query->where("role", "supervisor");
                })->first();
            }

            // fallback 2: kalo ga ada user dengan role "supervisor", maka gunakan employee pertama yang terdaftar
            $supervisorId = $defaultSupervisor ? $defaultSupervisor->id : (Employee::first()?->id ?? 1);

            $exists = Employee::where("user_id", $user->id)->exists();

            if(!$exists){
                Employee::create([
                    "user_id" => $user->id,
                    "department_id" => $deptId,
                    "position_id" => $application->vacancy->position->id,
                    "supervisor_id" => $supervisorId,
                    "join_date" => now()->toDateString(),
                    "contract_status" => "probation",
                ]);
            }

            DB::commit();
            Flux::toast("Kandidat {$candidate->name} berhasil diterima dan ditambahkan ke tabel employee.");
        }catch(\Exception $e){
            DB::rollback();
            Flux::toast("Gagal memproses penerimaan: " . $e->getMessage(), variant: "danger");
        }
    }

    public function decideRejected(int $applicationId){
        try{
            $application = Application::findOrFail($applicationId);

            // update keputusan di tabel recruitment_results
            if($application->result){
                $application->result->decission = "rejected";
                $application->result->save();
            }else{
                RecruitmentResult::create([
                    "application_id" => $applicationId,
                    "final_score" => 0.0,
                    "ranking" => 99,
                    "decission" => "rejected",
                ]);
            }

            // update status lamaran ke "rejected"
            $application->status = "rejected";
            $application->save();

            Flux::toast("Kandidat berhasil ditolak.");
        }catch(\Exception $e){
            Flux::toast("Gagal menolak kandidat: " . $e->getMessage(), variant: "danger");
        }
    }
};
?>

<div class="flex flex-1 flex-col gap-6">
    <livewire:bread-crumbs/>
    @php $result = $this->result(); @endphp
    @if(is_string($result))
        <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm text-center">
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-full mb-4">
                <flux:icon name="exclamation-triangle" class="size-10 text-amber-500" />
            </div>
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Perhitungan Tidak Dapat Dilakukan</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 max-w-md">
                {{ $result }}
            </p>
            <flux:button class="mt-8 cursor-pointer" href="{{ route('hr.dss') }}" wire:navigate icon="arrow-left" variant="primary">
                Kembali ke Daftar DSS
            </flux:button>
        </div>
    @else
        {{-- Header / Top Actions --}}
        <div class="flex items-center justify-between gap-4 bg-zinc-50 dark:bg-zinc-800/20 px-4 py-3 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
            <div class="flex items-center gap-2">
                <flux:icon name="briefcase" class="size-4 text-zinc-400 dark:text-zinc-500" />
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Lowongan:</span>
                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['vacancy'] }}</span>
            </div>
            <flux:button icon="arrow-left" size="sm" href="{{ route('hr.dss') }}" wire:navigate class="cursor-pointer">
                Kembali
            </flux:button>
        </div>
        <flux:separator/>
        {{-- Info Umum --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Posisi</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $result['position'] }}</span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Departemen</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $result['department'] }}</span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Deadline</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                    {{ \Carbon\Carbon::parse($result['deadline'])->translatedFormat('d M Y') }}
                </span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Preferensi (Pi)</span>
                <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $result['Pi'] }}</span>
            </div>
        </div>
        {{-- Info Kriteria --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400 flex items-center gap-2">
                <flux:icon name="clipboard-document-list" class="size-4 text-zinc-400" />
                Kriteria Penilaian & Bobot
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($result['criteria'] as $index => $criteriaName)
                    @php
                        $isBenefit = $result['types'][$index] === 'benefit';
                    @endphp
                    <div class="relative group overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-4 transition-all duration-300 hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 flex flex-col justify-between gap-3">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">
                                Kriteria {{ $index + 1 }}
                            </span>
                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-100 text-sm line-clamp-2 min-h-[2.5rem]" title="{{ $criteriaName }}">
                                {{ $criteriaName }}
                            </h3>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-zinc-100 dark:border-zinc-700/60">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded font-mono">
                                {{ number_format($result['weights'][$index] * 100, 0) }}%
                            </span>
                            <flux:badge size="sm" color="{{ $isBenefit ? 'green' : 'red' }}" class="capitalize font-medium">
                                {{ $result['types'][$index] }}
                            </flux:badge>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Detail Perhitungan MAIRCA --}}
        <div x-data="{ showDetails: false }" class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon name="calculator" class="size-4"/>
                    Langkah Perhitungan MAIRCA
                </h2>
                <flux:button size="sm" variant="subtle" class="cursor-pointer" x-on:click="showDetails = !showDetails">
                    <span x-text="showDetails ? 'Sembunyikan Detail' : 'Tampilkan Detail'"></span>
                </flux:button>
            </div>

            <div x-cloak x-show="showDetails" style="display: none;" x-transition class="flex flex-col gap-8 mt-2">
                {{-- 1. Decision Matrix --}}
                <div class="flex flex-col gap-2 overflow-x-auto">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">1. Matriks Keputusan (Decision Matrix)</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                                X = [x_ij]
                            </p>
                            <p class="text-[11px] text-zinc-500 mt-1">Matriks awal yang berisi nilai asli dari setiap kandidat (i) pada tiap kriteria (j).</p>
                        </div>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Alternatif / Kandidat</flux:table.column>
                            @foreach($result['criteria'] as $criteriaName)
                                <flux:table.column>{{ $criteriaName }}</flux:table.column>
                            @endforeach
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($result['alternatives'] as $i => $alt)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $alt }}</flux:table.cell>
                                    @foreach($result['decision_matrix'][$i] as $val)
                                        <flux:table.cell>{{ $val }}</flux:table.cell>
                                    @endforeach
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                {{-- 2. Normalized Matrix --}}
                <div class="flex flex-col gap-2 overflow-x-auto">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">2. Matriks Normalisasi (Normalized Matrix)</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                                Benefit: n_ij = (x_ij - x_min) / (x_max - x_min)<br/>
                                Cost: n_ij = (x_max - x_ij) / (x_max - x_min)
                            </p>
                            <p class="text-[11px] text-zinc-500 mt-1">Normalisasi nilai menggunakan rumus berbeda tergantung pada jenis kriterianya (Benefit atau Cost).</p>
                        </div>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Alternatif / Kandidat</flux:table.column>
                            @foreach($result['criteria'] as $criteriaName)
                                <flux:table.column>{{ $criteriaName }}</flux:table.column>
                            @endforeach
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($result['alternatives'] as $i => $alt)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $alt }}</flux:table.cell>
                                    @foreach($result['normalized_matrix'][$i] as $val)
                                        <flux:table.cell>{{ number_format($val, 4) }}</flux:table.cell>
                                    @endforeach
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                {{-- 3. Theoretical Matrix --}}
                <div class="flex flex-col gap-2 overflow-x-auto">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">3. Matriks Teoritis (Theoretical Matrix)</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                                t_ij = P_i × w_j
                            </p>
                            <p class="text-[11px] text-zinc-500 mt-1">Di mana P_i = 1 / jumlah kandidat (Preferensi), dan w_j adalah bobot tiap kriteria.</p>
                        </div>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Alternatif / Kandidat</flux:table.column>
                            @foreach($result['criteria'] as $criteriaName)
                                <flux:table.column>{{ $criteriaName }}</flux:table.column>
                            @endforeach
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($result['alternatives'] as $i => $alt)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $alt }}</flux:table.cell>
                                    @foreach($result['theoretical_matrix'][$i] as $val)
                                        <flux:table.cell>{{ number_format($val, 4) }}</flux:table.cell>
                                    @endforeach
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                {{-- 4. Actual Matrix --}}
                <div class="flex flex-col gap-2 overflow-x-auto">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">4. Matriks Aktual (Actual Matrix)</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                                tr_ij = t_ij × n_ij
                            </p>
                            <p class="text-[11px] text-zinc-500 mt-1">Didapatkan dari perkalian Matriks Teoritis (t_ij) dengan Matriks Normalisasi (n_ij).</p>
                        </div>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Alternatif / Kandidat</flux:table.column>
                            @foreach($result['criteria'] as $criteriaName)
                                <flux:table.column>{{ $criteriaName }}</flux:table.column>
                            @endforeach
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($result['alternatives'] as $i => $alt)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $alt }}</flux:table.cell>
                                    @foreach($result['actual_matrix'][$i] as $val)
                                        <flux:table.cell>{{ number_format($val, 4) }}</flux:table.cell>
                                    @endforeach
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                {{-- 5. Gap Matrix --}}
                <div class="flex flex-col gap-2 overflow-x-auto">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">5. Matriks Jarak / Evaluasi (Gap Matrix) & Qi</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 font-mono">
                                g_ij = |t_ij - tr_ij|<br/>
                                Qi = Σ g_ij
                            </p>
                            <p class="text-[11px] text-zinc-500 mt-1">Selisih absolut antara Matriks Teoritis dan Aktual. Total dari g_ij merupakan nilai Qi (semakin kecil semakin baik).</p>
                        </div>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Alternatif / Kandidat</flux:table.column>
                            @foreach($result['criteria'] as $criteriaName)
                                <flux:table.column>{{ $criteriaName }}</flux:table.column>
                            @endforeach
                            <flux:table.column>Nilai Qi (Total)</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($result['alternatives'] as $i => $alt)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">{{ $alt }}</flux:table.cell>
                                    @foreach($result['gap_matrix'][$i] as $val)
                                        <flux:table.cell>{{ number_format($val, 4) }}</flux:table.cell>
                                    @endforeach
                                    <flux:table.cell class="font-bold">{{ number_format($result['qi_scores'][$i], 4) }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        </div>

        <flux:separator/>

        {{-- Tabel Ranking --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                <flux:icon name="trophy" class="size-4"/>
                Peringkat Kandidat
            </h2>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Peringkat</flux:table.column>
                    <flux:table.column>Nama Kandidat</flux:table.column>
                    @foreach($result['criteria'] as $criteriaName)
                        <flux:table.column>{{ $criteriaName }}</flux:table.column>
                    @endforeach
                    <flux:table.column>Total Qi</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($result['ranking'] as $row)
                        <flux:table.row :key="$row['rank']">
                            {{-- Peringkat dengan warna medal --}}
                            <flux:table.cell>
                                @if($row['rank'] === 1)
                                    <flux:badge color="yellow" size="sm" icon="trophy">🥇 Ke-1</flux:badge>
                                @elseif($row['rank'] === 2)
                                    <flux:badge color="zinc" size="sm">🥈 Ke-2</flux:badge>
                                @elseif($row['rank'] === 3)
                                    <flux:badge color="zinc" size="sm">🥉 Ke-3</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Ke-{{ $row['rank'] }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            {{-- Nama kandidat --}}
                            <flux:table.cell>
                                <span class="font-medium {{ $row['rank'] === 1 ? 'text-amber-600 dark:text-amber-400' : '' }}">
                                    {{ $row['candidate_name'] }}
                                </span>
                            </flux:table.cell>
                            {{-- Gap per kriteria --}}
                            @foreach($row['gap_details'] as $gap)
                                <flux:table.cell>
                                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ number_format($gap, 6) }}
                                    </span>
                                </flux:table.cell>
                            @endforeach
                            {{-- Total Qi --}}
                            <flux:table.cell>
                                <span class="font-mono font-semibold {{ $row['rank'] === 1 ? 'text-green-600 dark:text-green-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ number_format($row['qi_score'], 6) }}
                                </span>
                            </flux:table.cell>
                            {{-- Aksi (Tombol Terima / Tolak) --}}
                            <flux:table.cell>
                                @php
                                    $appModel = \App\Models\Application::find($row['application_id']);
                                    $decisionStatus = $appModel?->status;
                                @endphp
                                @if($decisionStatus === 'hired')
                                    <flux:badge color="green">Diterima</flux:badge>
                                @elseif($decisionStatus === 'rejected')
                                    <flux:badge color="red">Ditolak</flux:badge>
                                @else
                                    <div class="flex gap-2">
                                        <flux:button size="xs" color="green" class="cursor-pointer" wire:click="decideHired({{ $row['application_id'] }})" wire:confirm="Terima kandidat ini dan masukkan langsung sebagai karyawan baru (Employee)?">
                                            Terima
                                        </flux:button>
                                        <flux:button size="xs" color="red" class="cursor-pointer" wire:click="decideRejected({{ $row['application_id'] }})" wire:confirm="Tolak kandidat ini?">
                                            Tolak
                                        </flux:button>
                                    </div>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                * Nilai Qi (Total Gap) yang lebih kecil menunjukkan kandidat yang lebih baik.
            </p>
        </div>
    @endif
</div>