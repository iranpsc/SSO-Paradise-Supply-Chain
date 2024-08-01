<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements ToArray, WithChunkReading, ShouldQueue
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function array(array $row)
    {
        foreach ($row as $key => $value) {
            if ($key === 0) {
                continue;
            }

            $userExists = User::where('email', $value[1])->exists();

            if ($userExists) {
                continue;
            }

            $user = User::create([
                'name' => $value[0],
                'email' => $value[1],
                'password' => Hash::make(Str::random(8)),
                'code' => $this->generateCode(),
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $user->personalInfo()->create();
        }
    }

    /**
     * Generate user code.
     *
     * @return string
     */
    public function generateCode()
    {
        $lastCode = User::max('code');

        if (is_null($lastCode)) {
            $lastCode = 'hm-2000000';
        } else {
            $lastCode = substr($lastCode, 3);
            $lastCode = intval($lastCode);
            $lastCode++;
            $lastCode = str_pad($lastCode, 6, '0', STR_PAD_LEFT);
            $lastCode = 'hm-' . $lastCode;
        }

        return $lastCode;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
