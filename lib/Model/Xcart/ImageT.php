<?php
class Model_Xcart_ImageT extends Model_Table2 {
  public $table='xcart_images_T';
  public $id_field='imageid';
  public $title_field='filename';
  function init() {
    parent::init();
    /*
      INSERT INTO xcart_images_T (`image_size`, `md5`, `filename`, `image_type`, `image_x`, `image_y`, `image_path`, `id`, `date`, `imageid`) VALUES ('34397', 'c33ad1e36577809e78a8ecd270d9754c', '0-761345-00401-5.jpg', 'image/jpeg', '400', '400', './images/T/0-761345-00401-5.jpg', '17516', '1331308478', '')
    */
    $this->addField('id');
    $this->addField('image_path');
    $this->addField('image_type')->defaultValue('image/jpeg');
    $this->addField('image_x');
    $this->addField('image_y');
    $this->addField('image_size');
    $this->addField('filename');
    $this->addField('date');
    $this->addField('avail')->defaultValue('Y');
    $this->addField('orderby')->defaultValue(0);
    $this->addField('md5');

  }
}

