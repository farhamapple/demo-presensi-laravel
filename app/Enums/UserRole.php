<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Pegawai = 'pegawai';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin SDM',
            self::Pegawai => 'Pegawai',
        };
    }
}
