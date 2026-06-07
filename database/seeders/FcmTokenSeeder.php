<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Database\Seeder;

class FcmTokenSeeder extends Seeder
{
    public function run(): void
    {
        $hr1 = User::where('email', 'hr1@hris.test')->first();
        $hr2 = User::where('email', 'hr2@hris.test')->first();

        // Aplikasi aktif (bukan hired/rejected) untuk contoh kandidat
        $appOpen1 = Application::where('application_code', 'APP-2026-00003')->first(); // Siti (interview_done)
        $appOpen2 = Application::where('application_code', 'APP-2026-00004')->first(); // Dewi (interview_scheduled)

        $tokens = [
            [
                'owner_type'  => 'hr',
                'owner_id'    => $hr1->id,
                'fcm_token'   => 'eA8rY6zPTFqZ4xX_H3jP9D:APA91bE3T-J8K-M7nB3rY6zP_H3jP9D_H3jP9D_H3jP9DeA8rY6zPTFqZ4xX_H3jP9DAPA91bE3T-J8K-M7nB3rY6zP_H3jP9DeA8rY6zPTFqZ4xX_H3jP9DAPA91bE3T-J8K',
            ],
            [
                'owner_type'  => 'hr',
                'owner_id'    => $hr2->id,
                'fcm_token'   => 'dG4sV7xLWEpW3yW_G2iO8C:BOB82cD4U-K9L-N8oC4sV7xL_G2iO8C_G2iO8C_G2iO8CdG4sV7xLWEpW3yW_G2iO8CBOB82cD4U-K9L-N8oC4sV7xL_G2iO8CdG4sV7xLWEpW3yW_G2iO8CBOB82cD4U-K9L',
            ],
            [
                'owner_type'  => 'candidate',
                'owner_id'    => $appOpen1->id,
                'fcm_token'   => 'cH3qU5wMSDoV2xV_F1hN7B:ANA71bC2S-I7J-L6mA2qU5wM_F1hN7B_F1hN7B_F1hN7BcH3qU5wMSDoV2xV_F1hN7BANA71bC2S-I7J-L6mA2qU5wM_F1hN7BcH3qU5wMSDoV2xV_F1hN7BANA71bC2S-I7J',
            ],
            [
                'owner_type'  => 'candidate',
                'owner_id'    => $appOpen2->id,
                'fcm_token'   => 'bF2pT4vLRCnU1wU_E0gM6A:ZMZ60aB1R-H6I-K5lZ1pT4vL_E0gM6A_E0gM6A_E0gM6AbF2pT4vLRCnU1wU_E0gM6AZMZ60aB1R-H6I-K5lZ1pT4vL_E0gM6AbF2pT4vLRCnU1wU_E0gM6AZMZ60aB1R-H6I',
            ],
        ];

        foreach ($tokens as $tokenData) {
            FcmToken::firstOrCreate(
                [
                    'owner_type' => $tokenData['owner_type'],
                    'owner_id'   => $tokenData['owner_id'],
                    'fcm_token'  => $tokenData['fcm_token'],
                ],
                $tokenData
            );
        }
    }
}
