<?php

class Block {
  public $row;
  public $column;
  public $width;
  public $blockNum;

  public function __construct($name, $width)
  {
    $this->name = $name;
    $this->width = $width;
  }

  public function setRow($row)
  {
    $this->row = $row;
  }
  public function setColumn($column)
  {
    $this->column = $column;
  }

  public function getColumn()
  {
    return $this->column;
  }

  public function getNumber()
  {
    return $this->blockNum;
  }

  public function getRow()
  {
    return $this->row;
  }

  public function getTime()
  {
    return 0;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function setNumber($number)
  {
    $this->blockNum = $number;
  }
}
