<?php

namespace OdoSha\Scytale\Encryptable\Traits;



trait Hasheable {
  
  /**
  **/
  public function getHasheables() {
    return $this->hasheables(); 
  }  
  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return mixed
   */
  public function setAttribute($key, $value)
  {
      if (is_null($value) || !in_array($key, $this->hasheable)) {
          return parent::setAttribute($key, $value);
      }

      $value = $this->hash($value);

      return parent::setAttribute($key, $value);
  }
  public function hash($value) {
    return hash(config('scytale.algo'), config('scytale.salt') . $value);
  }
}