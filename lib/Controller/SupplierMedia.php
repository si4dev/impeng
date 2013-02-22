<?php
class Controller_SupplierMedia extends AbstractController {
  function init() {
    parent::init();
  }

    // -----------------------------------------------------------------------------------------------
  // get for each supplier product the images and download when
  function importMedia($max) {
    $dir=$this->owner->get('name');
    $path=$this->api->getConfig('path_suppliermedia').$dir;
    if(!realpath($path)) throw $this->exception('Engine: supplier path does not exist') 
        ->addMoreInfo('path',$path)
        ;
    $path=realpath($path);

    $m=$this->add('Model_Media_Requested');
    // do not repeat media download try within 5 days
    $m->addExpression('days')->set('TO_DAYS(now()) - TO_DAYS(last_checked)');
    $m->addCondition('url','is not',null)
        ->addCondition('supplier_id',$this->owner->id)
        ->addCondition('days','>=',5) 
        ->addCondition('request_count','>',0) 
        ->setOrder('request_count','desc')
        ->setOrder('last_checked','asc')
        ;
        
    $m->_dsql()->limit($max);

    foreach($m as $media) {
        $m->set('file_dir',$dir)
            ->set('file_name',strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $media['productcode'] ), '-')) 
                .'_'.$media['purpose'].'_'.$m->id)
            ->set('file_ext','jpg')
            ;
        copy($media['url'],$path.'/'.$m->get('file_name').'.'.$m->get('file_ext'));
        $m->setFileInfo()
            ->set('last_checked',$m->dsql()->expr('now()'))
            ->validateImage()
            ->save();
    }
    return $this;
  }
  
  
}  
