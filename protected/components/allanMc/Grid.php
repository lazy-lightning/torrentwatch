<?php
class Grid {
  /**
    * usage: grid[ row ][ column ]
    *   columns
    * r 0 1 2 3 4
    * o 1
    * w 2
    * s 3
    *   4
    */
  private $grid;

  private $numRows;
  private $numColumns;
  private $numBlocks;

  private $lastRow;
  private $lastColumn;

  private $top;
  private $bottom;
  private $left;
  private $right;

  private $columnWidth;
  private $rowHeight;

  private $rowLeft;
  private $rowTop;

  public function __construct($numRows, $numColumns, $top, $bottom, $left, $right)
  {
    $this->top = $top;
    $this->bottom = $bottom;
    $this->left = $left;
    $this->right = $right;

    $this->columnWidth = (integer)(($right-$left)/$numColumns);
    $this->rowHeight = (integer)(($bottom-$top)/$numRows);

    for($i=0;$i<$numColumns;++$i)
      $this->rowLeft[$i] = $left+($i*$this->columnWidth);
    for($i=0;$i<$numRows;++$i)
      $this->rowTop[$i] = $top+($i*$this->rowHeight);

    $this->numRows = $numRows;
    $this->numColumns = $numColumns;;
    // Create a multidimensional array of the proper
    // dimensions with all values set false.
    $rows = array_fill(0,$numColumns, false);
    $this->grid = array_fill(0,$numRows,$rows);

    // initialize values so the blocks can be added from top right
    $this->lastRow = 0;
    $this->lastColumn = $numColumns-1;
  }

  public function addBlock(Block $block)
  {
    if($this->getNextPos())
    {
      $block->setRow($this->lastRow);
      $block->setColumn($this->lastColumn);
      $block->setNumber(++$this->numBlocks);
      $col = $this->lastColumn;
      for($width = $block->getWidth(); $width > 0; --$width)
      {
        $this->grid[$this->lastRow][$col--] = $block;
        // only actually attach the block the first time, the rest just mark true
        $block = true;
      }
      return true;
    }
    return false;
  }

  public function get($row, $col)
  {
    if($row < 0 || $row >= $this->numRows || $col < 0 || $col >= $this->numColumns)
      throw new CException('Accessing items outside valid grid of '.$this->numRows.' by '.$this->numColumns);
    if($this->grid[$row][$col] === true)
    {
      // shift right till we find the item;
      while($this->grid[$row][$col] === true)
        ++$col;
    }
    return $this->grid[$row][$col];
  }

  public function getRows()
  {
    return $this->grid;
  }

  private function getNextPos()
  {
    // Simple algorithem, add boxes from top right
    // to bottom right, moving left once a column is full
    // if an item has already taken the space skip it
    for($col=$this->lastColumn;$col >= 0;--$col)
    {
      for($row=$this->lastRow;$row < $this->numRows; ++$row)
      {
        if(false === $this->grid[$row][$col])
        {
          $this->lastColumn = $col;
          $this->lastRow = $row;
          return true;
        }
      }
      $this->lastRow = 0;
    }
    // grid is full
    return false;
  }

  public function getColumnWidth()
  {
    return $this->columnWidth;
  }

  public function getNumColumns()
  {
    return $this->numColumns;
  }

  public function getNumRows()
  {
    return $this->numRows;
  }

  public function getRowHeight()
  {
    return $this->rowHeight;
  }

  public function output($width = 35)
  {
    foreach($this->grid as $row)
    {
      foreach($row as $column)
      {
        printf('%'.$width.'s', $column instanceof Block?$column->name:'');
      }
      echo "\n";
    }
  }

  public function getBlocksCss()
  {
    $css = array();
    foreach($this->grid as $row=>$rows)
    {
      foreach($rows as $col=>$block)
      {
        if(false === $block instanceof Block)
          continue;
        $top = $this->rowTop[$row];
        $width = $block->getWidth()*$this->columnWidth;
        $left = $this->rowLeft[$col-$block->getWidth()+1];
        $css[] = sprintf('#block%-2d{left:%d;top:%d;width:%d;height;%d;position:absolute;}',
            $block->getNumber(), $left, $top, $width, $this->rowHeight);
      }
    }
    return $css;
  }

  public function getRowHeaderCss()
  {
    $left = $this->left - $this->columnWidth;
    // 21 is the number of pixels padding the glow image adds
    $top = $this->top+21;
    $css = array();
    for($i=0;$i<$this->numRows;++$i)
    {
      $css[] = sprintf('#row%-2d{left:%d;top%d;width:%d;position:absolute;}',
          $i,$left,$top,$this->columnWidth);
      $top += $this->rowHeight;
    }
    return $css;
  }

  public function getColHeaderCss()
  {
    // 26 is the number of pixels padding the glow image adds
    $left = $this->left+26;
    $css = array();
    for($i=0;$i<$this->numColumns;++$i)
    {
      $css[] = sprintf('#column%-2d{left:%d;}', $i, $left);
      $left += $this->columnWidth;
    }
    return $css;
  }
}
