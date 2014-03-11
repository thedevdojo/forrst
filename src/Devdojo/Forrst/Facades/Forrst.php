<?php namespace Devdojo\Forrst\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class Forrst extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'forrst'; }
 
}