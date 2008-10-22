<?php

require_once 'Zend/Filter/Interface.php';

class Kim_Filter_Slug implements Zend_Filter_Interface
{
    public function filter($value)
    {
      $cleanString = strtolower($value);

      // strip all non word chars
      $cleanString = preg_replace('/\W/', ' ', $cleanString);

      // replace all white space sections with a dash
      $cleanString = preg_replace('/\ +/', '-', $cleanString);

      // trim dashes
      $cleanString = preg_replace('/\-$/', '', $cleanString);
      $cleanString = preg_replace('/^\-/', '', $cleanString);

      if (strlen($cleanString) == 0) {
        return null;
      }

      return $cleanString;
    }
}