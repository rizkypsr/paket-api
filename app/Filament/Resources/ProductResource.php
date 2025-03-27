<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = "Paket";

    protected static ?string $pluralModelLabel = "Paket";

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'Paket';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('receipt_number')
                    ->required()
                    ->maxLength(255)
                    ->label('Nomor Resi')
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('delivery_id')
                    ->relationship('delivery', 'name')
                    ->required()
                    ->label('Jenis Pengiriman'),
                Forms\Components\Select::make('status_product_id')
                    ->relationship('status', 'name')
                    ->required()
                    ->label('Status'),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan'),
                Forms\Components\FileUpload::make('image')
                    ->required()
                    ->label('Sebelum'),
                Forms\Components\FileUpload::make('unboxing_image')
                    ->required()
                    ->label('Sesudah'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Nomor Resi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Dipreses Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery.name')
                    ->label('Jenis Pengiriman')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Sebelum'),
                Tables\Columns\ImageColumn::make('unboxing_image')
                    ->label('Sesudah'),
                Tables\Columns\TextColumn::make('description')->label('Keterangan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i:s'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_id')
                    ->relationship('delivery', 'name')
                    ->label('Jenis Pengiriman'),
                Tables\Filters\SelectFilter::make('status_product_id')
                    ->relationship('status', 'name')
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $month) {
                            return $query->whereMonth('created_at', $month);
                        });
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
