<?php

namespace OdoSha\Scytale\Encryptable\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
* shamelessly copied from https://github.com/betterapp/laravel-db-encrypter
*/
trait Encryptable
{
    /**
    * getEncryptables : get encryptables properties
    *
    * @return array
    **/
    public function getEncryptables(): array {
        return $this->encryptable;
    }
    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // decrypt value before casts
        if (in_array($key, $this->encryptable)) {
            $value = $this->decrypt($value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates()) &&
            ! is_null($value)) {
            return $this->asDateTime($value);
        }
        return $value;
    }
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (is_null($value) || !in_array($key, $this->encryptable)) {
            return parent::setAttribute($key, $value);
        }

        $value = $this->encrypt($value);

        return parent::setAttribute($key, $value);
    }

    /**
     * @return array
     */
    public function attributesToArray(): array
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableAttributes()
        );

        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // decrypt attributes before casts
        $attributes = $this->decryptAttributes($attributes);

        // Next we will handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        // Here we will grab all of the appended, calculated attributes to this model
        // as these attributes are not really in the attributes array, but are run
        // when we need to array or JSON the model for convenience to the coder.
        foreach ($this->getArrayableAppends() as $key) {
            $attributes[$key] = $this->mutateAttributeForArray($key, null);
        }
        /** relationship **/
        if (!empty($this->with)) {
            foreach ($this->with as $relation) {
                $attributes[$relation] = $this->decryptCollection($this->getRelation($relation));
                $attributes[$relation] = ($attributes[$relation])->toArray();
            }
        }
        return $attributes;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function decryptAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $this->encryptable) || is_null($value) || $value === '') {
                continue;
            }

            $attributes[$key] = $this->decrypt($value);
        }

        return $attributes;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function encrypt($value)
    {
        try {
            $value = Crypt::encrypt($value);
        } catch (EncryptException $e) {}

        return $value;
    }
    public function getEncryptable(): array {
        return $this->encryptable;
    }
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decrypt($value)
    {
        try {
            $value = Crypt::decrypt($value);
        } catch (DecryptException $e) {
            \Log::debug([$e->getMessage(), $value]);
        }

        return $value;
    }
    public function __call($method, $parameters)
    {
        if ($method == 'findOrFail' || $method == 'find') {
            $return = parent::__call($method, $parameters);
            return $this->decryptModel($return);
        }
        if ($method == 'all') {
          $return = parent::__call($method, $parameters);
          return $this->decryptCollection($return);
        }
        return parent::__call($method, $parameters);
    }
    public function decryptModel(Model $model): Model
    {
      foreach ($model->getEncryptable() as $attribute) {
          $model->setAttribute($attribute, $model->getAttribute($attribute));
      }

      return $model;
    }
    public function decryptCollection(Collection $collection): Collection
    {
      return $collection->map(function (Model $model) {
          return $this->decryptModel($model);
      });
    }
    /**
    * getAllAttributes: get all attributes 
    *
    * @return array 
    **/
    public function getAllAttributes(): array
    {
        $columns = $this->getFillable();
        // Another option is to get all columns for the table like so:
        // $columns = \Schema::getColumnListing($this->table);
        // but it's safer to just get the fillable fields

        $attributes = $this->getAttributes();

        foreach ($columns as $column)
        {
            if (!array_key_exists($column, $attributes))
            {
                $attributes[$column] = null;
            }
        }
        return $attributes;
    }
}