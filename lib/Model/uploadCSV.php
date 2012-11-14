<?php 


Class Model_uploadCSV extends filestore\Model_file {

	function getPath(){
        /* $path = 
            $this->ref("filestore_volume_id")->get("dirname") . "/" .
            $this['filename'];
        return $path; */
		
		return '../supplierdata/'.$this['original_filename'];
	}
	
	function performImport(){
		//check for user permissions
				
		$si=$this->add('Controller_Shopimport');
		$s=$si->shop;
		$supl_id = $s->ref('SupplierLink')->dsql()
			->field('supplier_id')
			->where('shop_id', $s['id']);
			
		$supl = $this->add('Model_Supplier');
		$supl->load($supl_id);
		
		if($this['original_filename'] == $supl['name'].'_pricelist.csv'){
			parent::performImport();
		}
		else
		{
			throw new exception("You can overrite only your supplier file");
		}
	}

	
}