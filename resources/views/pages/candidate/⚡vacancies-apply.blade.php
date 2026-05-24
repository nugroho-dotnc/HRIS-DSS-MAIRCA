<?php

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancies;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts::guest')] class extends Component
{
    use WithFileUploads;

    public $id;
    public $vacancy;

    #[Validate('required|string')]
    public $name = '';

    #[Validate('required|email:dns')]
    public $email = '';

    #[Validate('required')]
    public $phone = '';

    #[Validate('required|in:L,P')]
    public $gender = '';

    #[Validate('required')]
    public $city = '';

    #[Validate('required')]
    public $zip_code = '';

    #[Validate('required|file|mimes:pdf,doc,docx|max:5120')]
    public $cv_file;

    #[Validate('nullable|file|mimes:pdf,doc,docx|max:5120')]
    public $portofolio_file;

    #[Validate('required')]
    public $complete_address = '';

    #[Validate('nullable|url')]
    public $web_portofolio_url = '';

    #[Validate('nullable|string')]
    public $experience = '';

    public function mount(): void
    {
        $this->vacancy = Vacancies::with(['Hr', 'Position'])->findOrFail($this->id);
    }

    public function save(): void
    {
        $this->validate();

        $cvPath = null;
        $portofolioPath = null;

        DB::beginTransaction();

        try {
            [$candidate, $cvPath, $portofolioPath] = $this->createCandidate();
            $application = $this->createApplication($candidate->id);
            DB::commit();

            // mailer settings here ....

            session(['application_code' => $application->application_code]);

            $this->redirect(
                route('candidate.vacancies.applications-success'),
                navigate: true
            );

        } catch (QueryException $e) {
            DB::rollBack();

            if ($cvPath) Storage::disk('public')->delete($cvPath);
            if ($portofolioPath) Storage::disk('public')->delete($portofolioPath);

            $this->addError('email', 'Email ini sudah pernah digunakan.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($cvPath) Storage::disk('public')->delete($cvPath);
            if ($portofolioPath) Storage::disk('public')->delete($portofolioPath);

            $this->addError('name', 'Terjadi kesalahan, silakan coba lagi.');
        }
    }

    private function createCandidate(): array
    {
            // Simpan CV ke disk 'public' (storage/app/public)
            $cvFilename = 'cv_' . time() . '.' . $this->cv_file->getClientOriginalExtension();
            $cvPath = $this->cv_file->storeAs('candidates/cv', $cvFilename, 'public');

            // Simpan Portfolio (opsional)
            $portofolioPath = null;
            if ($this->portofolio_file) {
                $pfFilename = 'portfolio_' . time() . '.' . $this->portofolio_file->getClientOriginalExtension();
                $portofolioPath = $this->portofolio_file->storeAs('candidates/portfolio', $pfFilename, 'public');
            }

            $candidate = Candidate::create([
                'name'               => $this->name,
                'email'              => $this->email,
                'phone'              => $this->phone,
                'gender'             => $this->gender,
                'city'               => $this->city,
                'zip_code'           => $this->zip_code,
                'complete_address'   => $this->complete_address,
                'experience'         => $this->experience,
                'web_portofolio_url' => $this->web_portofolio_url,
                'cv_path'            => $cvPath,
                'portofolio_path'    => $portofolioPath,
            ]);

            return [$candidate, $cvPath, $portofolioPath];
    }

    private function createApplication($candidate_id):Application
    {
        return Application::create([
                'candidate_id'     => $candidate_id,
                'vacancy_id'       => $this->id,
                'application_code' => $this->generateApplicationCode(),
            ]);
    }

    private function generateApplicationCode(): string
    {
        $namePrefix = strtoupper(substr(preg_replace('/\s+/', '', $this->name), 0, 3));
        $datePart   = now()->format('dmy');
        $code       = $namePrefix . $datePart;

        while (Application::where('application_code', $code)->exists()) {
            $code = $namePrefix . $datePart . rand(100, 999);
        }

        return $code;
    }
};
?>

<div class="flex flex-1 flex-col gap-4 md:gap-6 rounded-xl">
    <livewire:bread-crumbs/>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl md:text-2xl font-bold text-zinc-900 dark:text-white">{{ $vacancy->title }}</h1>
            <span class="text-sm text-zinc-400">{{ $vacancy->Position->position_name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <flux:badge color="{{ $vacancy->status === 'open' ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                {{ ucfirst($vacancy->status) }}
            </flux:badge>
        </div>
    </div>
    <flux:fieldset class="space-y-4 md:space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 md:gap-y-6">
            <flux:input
                label="Nama"
                placeholder="ex: Jane Doe"
                wire:model="name"
                value="{{ old('name') }}"
            />
            <flux:input
                label="Email"
                type="email"
                placeholder="janedoe@example.com"
                wire:model="email"
                value="{{ old('email') }}"
            />
        </div>
        <flux:input
            label="Phone number"
            type="tel"
            placeholder="+62xxxxxxxxxxx"
            wire:model="phone"
            value="{{ old('phone') }}"
        />
        <flux:field class="mt-4">
            <flux:label>
                Jenis Kelamin
            </flux:label>
            <flux:radio.group wire:model="gender" value="{{ old('gender') }}">
                <flux:radio value="L" label="Laki - laki" />
                <flux:radio value="P" label="Perempuan" />
            </flux:radio.group>
        </flux:field>
    </flux:fieldset>

    <flux:separator text="address"/>

    <flux:fieldset class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 md:gap-y-6">
        <flux:input
            label="City"
            placeholder="San Francisco"
            wire:model="city"
            value="{{ old('city') }}"
        />
        <flux:input
            label="Postal / Zip code"
            placeholder="12345"
            wire:model="zip_code"
            value="{{ old('zip_code') }}"
        />
        <div class="flex flex-col gap-2 col-span-1 md:col-span-2">
            <flux:textarea
                wire:model="complete_address"
                label="Alamat Lengkap"
                rows="4"
                placeholder="Masukkan alamat lengkap anda..."
                value="{{ old('complete_address') }}"
            />
        </div>
    </flux:fieldset>

    <flux:separator text="experiences"/>

    <flux:fieldset class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 md:gap-y-6">
        <div class="col-span-1 md:col-span-2">
            <flux:textarea
                wire:model="experience"
                label="Experiences"
                rows="4"
                placeholder="Tuliskan pengalaman anda yang berhubungan dengan posisi ini..."
                value="{{ old('experience') }}"
            />
        </div>

        <flux:input
            label="CV"
            type="file"
            accept=".pdf,.doc,.docx"
            wire:model="cv_file"
        />

        <flux:input
            label="Portofolio"
            type="file"
            accept=".pdf,.doc,.docx"
            wire:model="portofolio_file"
        />

        <div class="col-span-1 md:col-span-2">
            <flux:input
                label="Web Portofolio URL"
                placeholder="https://nugroho.porto.com"
                wire:model="web_portofolio_url"
                value="{{ old('web_portofolio_url') }}"
            />
        </div>
    </flux:fieldset>

    {{-- loading indicator saat file diupload --}}
    <div wire:loading wire:target="cv_file,portofolio_file" class="text-sm text-zinc-400">
        Mengupload file...
    </div>

    <flux:button
        variant="primary"
        class="cursor-pointer"
        wire:click="save"
        wire:loading.attr="disabled"
    >
        <span wire:loading.remove wire:target="save">Apply</span>
        <span wire:loading wire:target="save">Applying...</span>
    </flux:button>
</div>
