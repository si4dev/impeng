<?php
class Model_Xcart_ImageP extends Model_Xcart {
  public $table='xcart_images_p';
  public $id_field='imageid';
  public $title_field='filename';
  function init() {
    parent::init();
    /*
      INSERT INTO xcart_images_P (`image_size`, `md5`, `filename`, `image_type`, `image_x`, `image_y`, `image_path`, `id`, `date`, `imageid`) VALUES ('32503', '9721b3cce26408574079a043aa5fcfe3', '0-761345-00050-5.jpg', 'image/jpeg', '400', '400', './images/P/0-761345-00050-5.jpg', '17515', '1331284555', '')
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

