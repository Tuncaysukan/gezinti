<?php

declare(strict_types=1);

namespace MiniORM;

use PDO;

/**
 * Abstract Model sınıfı
 * Tüm modeller bu sınıftan türemelidir
 */
abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $attributes = [];
    protected array $original = [];
    protected array $relations = [];
    protected static ?PDO $pdo = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    // Tablo adını al - yoksa class'tan türet
    protected function getTable(): string
    {
        if (empty($this->table)) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's'; // basit pluralization
        }
        return $this->table;
    }

    /**
     * Query Builder instance oluştur
     */
    public static function query(): QueryBuilder
    {
        $instance = new static();
        $builder = new QueryBuilder(self::getPdo());
        $builder->table($instance->getTable());
        return $builder;
    }

    /**
     * PDO instance al
     */
    protected static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = Database::getConnection();
        }
        return self::$pdo;
    }

    // Hepsini getir
    public static function all(): array
    {
        $results = static::query()->get();
        return static::hydrate($results);
    }

    // WHERE koşulu
    public static function where(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    // ID ile bul
    public static function find(int $id): ?static
    {
        $result = static::query()->find($id);
        return $result ? new static($result) : null;
    }

    /**
     * İlk kaydı al
     */
    public static function first(): ?static
    {
        $result = static::query()->first();
        
        if ($result === null) {
            return null;
        }

        return new static($result);
    }

    /**
     * Yeni kayıt oluştur
     */
    public static function create(array $attributes): static
    {
        $instance = new static();
        $instance->fill($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Kayıt sayısını döndür
     */
    public static function count(): int
    {
        return static::query()->count();
    }

    /**
     * Kayıt var mı kontrol et
     */
    public static function exists(): bool
    {
        return static::query()->exists();
    }

    /**
     * Kaydı güncelle (static)
     */
    public static function update(int $id, array $attributes): bool
    {
        $affected = static::query()
            ->where('id', '=', $id)
            ->update($attributes);
            
        return $affected > 0;
    }

    /**
     * Kaydı sil (static)
     */
    public static function delete(int $id): bool
    {
        $affected = static::query()
            ->where('id', '=', $id)
            ->delete();
            
        return $affected > 0;
    }

    /**
     * Eager loading ile kayıtları al
     */
    public static function with(string|array $relations): QueryBuilder
    {
        return static::query()->with($relations);
    }

    // Kaydet - yeni kayıt mı yoksa güncelleme mi karar ver
    public function save(): bool
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->performUpdate(); // güncelleme
        } else {
            return $this->performInsert(); // yeni kayıt
        }
    }

    /**
     * INSERT işlemi gerçekleştir
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getAttributesForInsert();
        
        if (empty($attributes)) {
            return false;
        }

        $id = static::query()->insert($attributes);
        
        $this->attributes[$this->primaryKey] = $id;
        $this->original = $this->attributes;
        
        return true;
    }

    /**
     * UPDATE işlemi gerçekleştir
     */
    protected function performUpdate(): bool
    {
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true; // Değişiklik yok
        }

        $affected = static::query()
            ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
            ->update($dirty);

        $this->original = $this->attributes;

        return $affected > 0;
    }

    /**
     * Modeli sil (instance method)
     */
    public function destroy(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        $affected = static::query()
            ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
            ->delete();

        return $affected > 0;
    }

    /**
     * Attributes'u doldur
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Kolon doldurulabilir mi?
     */
    protected function isFillable(string $key): bool
    {
        // Eğer fillable boş ise her şeye izin ver
        if (empty($this->fillable)) {
            return true;
        }
        
        return in_array($key, $this->fillable);
    }

    /**
     * INSERT için attributes al
     */
    protected function getAttributesForInsert(): array
    {
        $attributes = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey && $this->isFillable($key)) {
                $attributes[$key] = $value;
            }
        }
        
        return $attributes;
    }

    /**
     * Değişen (dirty) attributes'ları al
     */
    protected function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key === $this->primaryKey) {
                continue;
            }
            
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }

    /**
     * Array sonuçlarını Model instance'larına dönüştür
     */
    protected static function hydrate(array $results): array
    {
        $models = [];
        
        foreach ($results as $result) {
            $models[] = new static($result);
        }
        
        return $models;
    }

    /**
     * Modeli array'e dönüştür
     */
    public function toArray(): array
    {
        $array = $this->attributes;
        
        // İlişkileri de ekle
        foreach ($this->relations as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $array[$key] = array_map(function ($item) {
                    return $item instanceof self ? $item->toArray() : $item;
                }, $value);
            }
        }
        
        return $array;
    }

    /**
     * Modeli JSON'a dönüştür
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    // Magic getter - attribute'lara ve ilişkilere erişim
    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // Metod varsa çağır (lazy loading)
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            $this->relations[$key] = $relation;
            return $relation;
        }

        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    // ============================================
    // İLİŞKİLER
    // ============================================

    // belongsTo (Many-to-One) - Post belongs to User
    protected function belongsTo(string $relatedModel, ?string $foreignKey = null, ?string $ownerKey = null): mixed
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($relatedModel))->getShortName()) . '_id';
        $ownerKey = $ownerKey ?? 'id';

        if (!isset($this->attributes[$foreignKey])) {
            return null;
        }

        return $relatedModel::where($ownerKey, '=', $this->attributes[$foreignKey])->first();
    }

    // hasMany (One-to-Many) - User has many Posts
    protected function hasMany(string $relatedModel, ?string $foreignKey = null, ?string $localKey = null): array
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $localKey = $localKey ?? $this->primaryKey;

        if (!isset($this->attributes[$localKey])) {
            return [];
        }

        $results = $relatedModel::where($foreignKey, '=', $this->attributes[$localKey])->get();
        return static::hydrateRelated($relatedModel, $results);
    }

    /**
     * hasOne ilişkisi
     * Örnek: User->profile() => Profile modeli döner
     */
    protected function hasOne(string $relatedModel, ?string $foreignKey = null, ?string $localKey = null): mixed
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $localKey = $localKey ?? $this->primaryKey;

        if (!isset($this->attributes[$localKey])) {
            return null;
        }

        return $relatedModel::where($foreignKey, '=', $this->attributes[$localKey])->first();
    }

    /**
     * belongsToMany ilişkisi (Many-to-Many)
     * Örnek: User->roles() => Role[] döner
     */
    protected function belongsToMany(
        string $relatedModel,
        ?string $pivotTable = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null
    ): array {
        $relatedInstance = new $relatedModel();
        
        // Pivot tablo adını oluştur
        if ($pivotTable === null) {
            $tables = [
                strtolower((new \ReflectionClass($this))->getShortName()),
                strtolower((new \ReflectionClass($relatedModel))->getShortName())
            ];
            sort($tables);
            $pivotTable = implode('_', $tables);
        }

        $foreignPivotKey = $foreignPivotKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $relatedPivotKey = $relatedPivotKey ?? strtolower((new \ReflectionClass($relatedModel))->getShortName()) . '_id';

        if (!isset($this->attributes[$this->primaryKey])) {
            return [];
        }

        // JOIN ile many-to-many sorgu
        $results = static::query()
            ->table($relatedInstance->getTable())
            ->join(
                $pivotTable,
                $relatedInstance->getTable() . '.id',
                '=',
                $pivotTable . '.' . $relatedPivotKey
            )
            ->where($pivotTable . '.' . $foreignPivotKey, '=', $this->attributes[$this->primaryKey])
            ->get();

        return static::hydrateRelated($relatedModel, $results);
    }

    /**
     * İlişkili modelleri hydrate et
     */
    protected static function hydrateRelated(string $modelClass, array $results): array
    {
        $models = [];
        
        foreach ($results as $result) {
            $models[] = new $modelClass($result);
        }
        
        return $models;
    }
}

