<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $navigationLabel = 'Mis Productos';
    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $activeNavigationIcon = 'heroicon-s-building-storefront';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->required()
                                    ->default(function () {
                                        $lastProducto = Producto::orderBy('id', 'desc')->first()?->codigo;
                                        if ($lastProducto != null) {
                                            return $lastProducto + 1;
                                        } else return 10001;
                                    })
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('descripcion')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('categoria_id')
                                    ->relationship('categoria', 'nombre')
                                    ->required(),
                                Forms\Components\Select::make('laboratorio_id')
                                    ->relationship('laboratorio', 'nombre')
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\FileUpload::make('imagen')
                                    ->required(false)
                                    ->acceptedFileTypes(['images/*'])
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Checkbox::make('impuesto')
                                    ->default(true)
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Components\Checkbox $component, bool $state) => $component
                                        ->getContainer()
                                        ->getComponent('porcentaje')
                                        ->disabled(!$state)),
                                Forms\Components\TextInput::make('porcentaje')
                                    ->prefix('%')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->dehydrated()
                                    ->key('porcentaje')
                                    ->disabled(fn (Forms\Get $get) => !$get('impuesto')),
                                Forms\Components\TextInput::make('precio_compra')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, string|null $state, Forms\Components\TextInput $component) {
                                        // dd($get('impuesto'));
                                        $iva = ($get('porcentaje') / 100) + 1;
                                        if ($get('impuesto')) {
                                            $set('precio_venta', number_format($state * $iva, 2));
                                            $component->getContainer()
                                                ->getComponent('precio_venta')
                                                ->disabled(true)
                                                ->dehydrated();
                                        }
                                    }),
                                Forms\Components\TextInput::make('precio_venta')
                                    ->required()
                                    ->key('precio_venta')
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('precio_mayor')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01),
                                Forms\Components\TextInput::make('stock')
                                    ->required()
                                    ->numeric(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('laboratorio.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->searchable(),
                Tables\Columns\TextColumn::make('precio_compra')
                    ->searchable(),
                Tables\Columns\TextColumn::make('precio_venta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('precio_mayor')
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
                // Tables\Columns\TextColumn::make('last_sale.created_at')
                //     ->label('Last Sale')
                //     ->dateTime()
                //     ->sortable(),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
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
