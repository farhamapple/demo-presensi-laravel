<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pegawai')
                    ->description('Lengkapi data identitas pegawai untuk keperluan presensi.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('nip')
                                ->label('NIP')
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(50),

                            TextInput::make('email')
                                ->label('Alamat Email')
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Nomor Telepon / WhatsApp')
                                ->tel()
                                ->maxLength(25),

                            TextInput::make('unit_kerja')
                                ->label('Unit Kerja')
                                ->placeholder('Contoh: Biro SDM')
                                ->maxLength(255),

                            TextInput::make('jabatan')
                                ->label('Jabatan')
                                ->placeholder('Contoh: Analis Kebijakan Ahli Pertama')
                                ->maxLength(255),

                            Select::make('role')
                                ->label('Peran / Hak Akses')
                                ->options(UserRole::class)
                                ->default(UserRole::Pegawai)
                                ->required(),

                            TextInput::make('password')
                                ->label('Kata Sandi')
                                ->password()
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText('Kosongkan jika tidak ingin mengubah kata sandi.'),

                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->helperText('Hanya pegawai aktif yang dapat melakukan presensi.')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
