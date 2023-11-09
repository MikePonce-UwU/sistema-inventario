<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Filament\Resources\VentaResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;
    protected static ?string $modelLabel = 'Venta';
    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?string $navigationLabel = 'Mis Ventas';
    protected static ?string $navigationGroup = 'Ventas';

    protected static ?string $activeNavigationIcon = 'heroicon-s-currency-dollar';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('codigo')
                                    ->required()
                                    ->default(function () {
                                        $lastEntrada = Venta::orderByDesc('id')->first()?->codigo;
                                        if ($lastEntrada != null) {
                                            return $lastEntrada + 1;
                                        } else return 10001;
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('cliente_id')
                                    ->relationship('cliente', 'id')
                                    ->required()
                                    ->hint('Cliente al que le facturamos')
                                    ->helperText(str('Si el cliente no existe, puede **crear** uno')->inlineMarkdown()->toHtmlString())
                                    ->options(Cliente::all()->pluck('nombre_comercial', 'id'))
                                    ->relationship('cliente', 'nombre_comercial')
                                    ->createOptionForm([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('nombre_contacto')
                                                    ->required(),
                                                Forms\Components\TextInput::make('cedula_contacto')
                                                    ->required(),
                                                Forms\Components\TextInput::make('correo_contacto')
                                                    ->required(),
                                                Forms\Components\TextInput::make('telefono_contacto')
                                                    ->required(),
                                                Forms\Components\TextInput::make('nombre_comercial')
                                                    ->required(),
                                                Forms\Components\TextInput::make('documento_comercial')
                                                    ->required(),
                                                Forms\Components\TextInput::make('direccion')
                                                    ->required(),
                                                Forms\Components\TextInput::make('monto')
                                                    ->numeric(),
                                                Forms\Components\TextInput::make('dias')
                                                    ->numeric()
                                            ]),

                                    ])
                                /*->createOptionAction(fn($data) => dd($data))*/,
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->required(),
                            ]),
                        Forms\Components\Repeater::make('productos')
                            ->required()
                            ->reorderable(false)
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('producto_id')
                                            ->required()
                                            ->live(debounce: 1000)
                                            ->options(Producto::all()->pluck('descripcion', 'id')),
                                        Forms\Components\TextInput::make('cantidad')
                                            ->required()
                                            ->minValue(0)
                                            ->maxValue(function (Forms\Get $get) {
                                                if ($get('producto_id')) {
                                                    $cantidad = Producto::findOrFail($get('producto_id'))?->stock;
                                                    return $cantidad;
                                                }
                                            })
                                            ->dehydrated()
                                            ->default(fn (Forms\Get $get) => $get('producto_id') ? 0 : null)
                                            ->disabled(fn (Forms\Get $get) => !$get('producto_id'))
                                            ->numeric(),
                                        Forms\Components\Radio::make('tipo_precio')
                                            ->required()
                                            ->options([
                                                'precio_venta' => 'Precio venta',
                                                'precio_mayor' => 'Precio mayor',
                                            ])
                                            // ->descriptions([
                                            //     'precio_venta' => 'Precio al detalle.',
                                            //     'precio_mayor' => 'Precio al por mayor.',
                                            // ])
                                            ->live()
                                            ->afterStateUpdated(function (Forms\get $get, Forms\Set $set, ?string $state) {
                                                // dd($state);
                                                if ($get('producto_id')) {
                                                    $producto = Producto::findOrFail($get('producto_id'));
                                                    $set('precio_producto', $producto?->{$state});
                                                }
                                            })
                                            ->disabled(fn (Forms\Get $get) => !$get('producto_id')),
                                        Forms\Components\TextInput::make('precio_producto')
                                            ->required()
                                            ->numeric()
                                            ->dehydrated()
                                            ->disabled()
                                            ->step(0.01),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('impuesto')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('neto')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->dehydrated()
                                    ->hintAction(
                                        Forms\Components\Actions\Action::make('Actualizar Neto')
                                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                                if ($get('impuesto')){
                                                    $subtotales = 0.00;
                                                    foreach ($get('productos') as $key => $value) {
                                                        # code...
                                                        $subtotales += $value['cantidad'] * $value['precio_producto'];
                                                    }
                                                    // dump($subtotales);
                                                    $iva = ($get('impuesto') / 100) + 1;
                                                    $total_neto = number_format($subtotales * $iva, 2);
                                                    // dump($total_neto);
                                                    $set('neto', floatval($total_neto));
                                                }
                                            })
                                    ),
                                Forms\Components\Select::make('metodo_pago')
                                    ->required()
                                    ->options([
                                        'credito' => 'Crédito',
                                        'cheque' => 'Cheque',
                                        'efectivo' => 'Efectivo',
                                    ]),
                                Forms\Components\TextInput::make('referencia')
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nombre_comercial')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('impuesto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('neto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('metodo_pago')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referencia')
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
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
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
