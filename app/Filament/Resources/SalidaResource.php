<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalidaResource\Pages;
use App\Filament\Resources\SalidaResource\RelationManagers;
use App\Models\Producto;
use App\Models\Salida;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalidaResource extends Resource
{
    protected static ?string $model = Salida::class;

    protected static ?string $modelLabel = 'Salida';
    protected static ?string $pluralModelLabel = 'Salidas';

    protected static ?string $navigationLabel = 'Mis Salidas';
    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $activeNavigationIcon = 'heroicon-s-document-arrow-up';

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->required()->default(function () {
                                        $lastSalida = Salida::orderByDesc('id')->first()?->codigo;
                                        if ($lastSalida != null) {
                                            return $lastSalida + 1;
                                        } else return 10001;
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->required(),
                                Forms\Components\Select::make('from_id')
                                    ->relationship('from', 'nombre')
                                    ->different('to_id')
                                    ->live(onBlur: true, debounce: 1000)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, string|int|null $state) {
                                        if ($get('to_id') === $state) {
                                            Notification::make()
                                                ->title('Las bodegas no pueden coincidir.')
                                                ->danger()
                                                ->send();
                                            $set('from_id', null);
                                            $set('to_id', null);
                                            return;
                                        }
                                    })
                                    ->required(),
                                Forms\Components\Select::make('to_id')
                                    ->relationship('to', 'nombre')
                                    ->different('from_id')
                                    ->live(onBlur: true, debounce: 1000)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, string|int|null $state) {
                                        if ($get('from_id') === $state) {
                                            Notification::make()
                                                ->title('Las bodegas no pueden coincidir.')
                                                ->danger()
                                                ->send();
                                            $set('from_id', null);
                                            $set('to_id', null);
                                            return;
                                        }
                                    })
                                    ->required(),
                            ]),
                        Forms\Components\Repeater::make('productos')
                            ->required()
                            ->reorderable(false)
                            ->dehydrated()
                            ->disabled(fn (Forms\Get $get) => !$get('from_id'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('producto_id')
                                            ->required()
                                            ->live(debounce: 1000)
                                            ->dehydrated()
                                            ->options(fn(Forms\Get $get) => Producto::where('laboratorio_id', $get('from_id'))->get()->pluck('descripcion', 'id')),
                                        Forms\Components\TextInput::make('cantidad')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(function (Forms\Get $get) {
                                                if ($get('producto_id')) {
                                                    $cantidad = Producto::findOrFail($get('producto_id'))?->stock;
                                                    return $cantidad;
                                                }
                                            })
                                            ->dehydrated()
                                            ->default(fn (Forms\Get $get) => $get('producto_id') ? 1 : null)
                                            ->disabled(fn (Forms\Get $get) => !$get('producto_id')),
                                    ])
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Select::make('descripcion')
                            ->required()
                            ->options([
                                'merma' => 'MERMA',
                                'traslado_bodega' => 'Traslado de bodega',
                                'regalia' => 'Regalías',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListSalidas::route('/'),
            'create' => Pages\CreateSalida::route('/create'),
            'edit' => Pages\EditSalida::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
