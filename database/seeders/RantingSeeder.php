<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ranting;
use App\Models\RantingMember;
use Illuminate\Database\Seeder;

class RantingSeeder extends Seeder
{
    public function run(): void
    {
        $rantingData = [
            [
                'name' => 'Ranting Sukamaju',
                'slug' => 'ranting-sukamaju',
                'description' => 'Desa Sukamaju, Kecamatan Cibadak',
                'sort_order' => 1,
                'members' => [
                    'Ahmad Fauzi', 'M. Ridwan', 'Budi Santoso', 'Hendra Wijaya',
                    'Deni Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Galih Setiawan',
                    'Hadi Susanto', 'Ivan Firmansyah',
                ],
            ],
            [
                'name' => 'Ranting Mekarjaya',
                'slug' => 'ranting-mekarjaya',
                'description' => 'Desa Mekarjaya, Kecamatan Palabuhanratu',
                'sort_order' => 2,
                'members' => [
                    'Joko Prayitno', 'Kevin Saputra', 'Lukman Hakim', 'Muhamad Iqbal',
                    'Nanda Pratama', 'Oki Setiawan', 'Panji Kusuma', 'Rizky Aditya',
                ],
            ],
            [
                'name' => 'Ranting Bojongkerta',
                'slug' => 'ranting-bojongkerta',
                'description' => 'Desa Bojongkerta, Kecamatan Warungkiara',
                'sort_order' => 3,
                'members' => [
                    'Samsul Bahri', 'Teguh Santoso', 'Usman Harun', 'Wahyu Hidayat',
                    'Yusuf Al-Amin', 'Zulfikar Akbar', 'Andi Rahman', 'Bachtiar Effendi',
                    'Cahyono Putro', 'Darmawan Susilo', 'Erwansyah Putra',
                ],
            ],
        ];

        foreach ($rantingData as $data) {
            $members = $data['members'];
            unset($data['members']);

            $ranting = Ranting::query()->firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($members as $index => $memberName) {
                RantingMember::query()->firstOrCreate(
                    ['ranting_id' => $ranting->id, 'name' => $memberName],
                    ['sort_order' => $index + 1]
                );
            }
        }
    }
}
