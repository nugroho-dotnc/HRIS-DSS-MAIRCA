<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs;

    public function mount()
    {
        $this->breadcrumbs = $this->generate();
    }

    protected function generate(): array
    {
        $segments = request()->segments();
        $breadcrumbs = [];

        // Pemetaan kata teknis ke label bahasa Indonesia yang rapi
        $mapping = [
            'hr'           => 'HR',
            'dss'          => 'Hasil DSS',
            'result'       => 'Hasil Perhitungan',
            'vacancies'    => 'Lowongan',
            'applications' => 'Review Lamaran',
            'interviews'   => 'Sesi Interview',
            'create'       => 'Tambah Baru',
            'apply'        => 'Lamar Pekerjaan',
            'settings'     => 'Pengaturan',
            'profile'      => 'Profil',
            'security'     => 'Keamanan',
            'appearance'   => 'Tampilan',
            'admin'        => 'Admin',
            'departments'  => 'Departemen',
            'positions'    => 'Posisi Jabatan',
            'dashboard'    => 'Dashboard',
            'users'        => 'Kelola User',
            'success'      => 'Sukses',
        ];

        $url = '';
        foreach ($segments as $index => $segment) {
            $url .= '/' . $segment;

            // 1. Tentukan label dasar (apakah ada di kamus pemetaan atau tidak)
            $label = $mapping[strtolower($segment)] ?? ucfirst(str_replace('-', ' ', $segment));

            // 2. Jika segmen berupa ID numerik, selesaikan ke nama/judul aslinya dari DB
            if (is_numeric($segment)) {
                $prevSegment = $index > 0 ? strtolower($segments[$index - 1]) : '';
                try {
                    if ($prevSegment === 'dss' || $prevSegment === 'vacancies') {
                        $vacancy = \App\Models\Vacancies::find($segment);
                        if ($vacancy) {
                            $label = $vacancy->title;
                        }
                    } elseif ($prevSegment === 'applications') {
                        $app = \App\Models\Application::with('candidate')->find($segment);
                        if ($app && $app->candidate) {
                            $label = $app->candidate->name;
                        }
                    } elseif ($prevSegment === 'interviews') {
                        $session = \App\Models\InterviewSession::with('application.candidate')->find($segment);
                        if ($session && $session->application && $session->application->candidate) {
                            $label = 'DSS: ' . $session->application->candidate->name;
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback jika terjadi error query
                    $label = '#' . $segment;
                }
            }

            $breadcrumbs[] = [
                'label' => $label,
                'url'   => url($url),
            ];
        }

        return $breadcrumbs;
    }
};
?>

<div>
    <flux:breadcrumbs>
    @foreach ($this->breadcrumbs as $index => $crumb)
        @if (!$loop->last)
            <flux:breadcrumbs.item :href="$crumb['url']">
                {{ $crumb['label'] }}
            </flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>
                {{ $crumb['label'] }}
            </flux:breadcrumbs.item>
        @endif
    @endforeach
</flux:breadcrumbs>
</div>
