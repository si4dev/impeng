<?php

class page_modeljoin extends Page_DBTest {
    public $db;
    function init(){
        $this->db=$this->add('DB');

        try {
        $this->db->query('drop temporary table author');
        }catch(PDOException $e){}try{
        $this->db->query('drop temporary table book');
        }catch(PDOException $e){}try{
        $this->db->query('drop temporary table contact');
        }catch(PDOException $e){}
        $this->db->query('create temporary table author (id int not null primary key auto_increment, name varchar(255), email varchar(255))');
        $this->db->query('create temporary table book (id int not null primary key auto_increment, name varchar(255), isbn varchar(255), author_id int)');

        $this->db->query('create temporary table contact (id int not null primary key auto_increment, address varchar(255), author_id int)');


        parent::init();
    }
    function prepare(){
        $this->mb=$this->add('Model_BookAuthor');
        return array($this->mb->dsql);
    }
    function test_ref($q){
        $m1=$this->add('Model_Book');
        return $m1->ref('Author');
    }
    function test_j1(){
        $m=$this->add('Model_BookAuthor');
        return $m->dsql();
    }
    function test_j2(){
        $m=$this->add('Model_BookAuthor');
        $m->join('book_info','book_info_id');
        return $m->dsql();
    }
    function test_j3(){
        $m=$this->add('Model_BookAuthor');
        $m->set('name','John');
        $m->set('email','j@mail.com');
        $m->save();

        $m->set('name','Peter');
        $m['isbn']=123123;
        $m->save();

        return $m['name'];
    }
    function test_j4(){
        $m=$this->add('Model_AuthorBook');
        $m->set('name','John');
        $m->set('email','j@mail.com');
        $m->save();

        $m->set('name','Peter');
        $m['isbn']=123123;
        $m->save();

        return $m->id;
    }
    function test_j5(){
        try {
        $m=$this->add('Model_BookAuthorContact');
        $m->set('name','John');
        $m->set('email','j@mail.com');
        $m->save();

        $m->set('name','Peter');
        $m['isbn']=123123;
        $m['address']='IL7';
        $m->save();

        return $m->id;
        }catch(Exception $e){
            $this->api->caughtException($e);
        }
    }
}

class Model_Book extends Model_Table {
    public $table='book';
    function init(){
        parent::init();

        $this->addField('name');
        $this->addField('isbn');

        $this->hasOne('Author');
    }
}
class Model_Author extends Model_Table {
    public $table='author';
    function init(){
        parent::init();

        $this->addField('name');
        $this->addField('email');

        //$this->hasMany('Book');
    }
}

class Model_BookAuthor extends Model_Book {
    public $a;
    function init(){
        parent::init();
        $this->a=$this->join('author');
        $this->a->addField('email');
    }
}

class Model_AuthorBook extends Model_Author {
    public $b;
    function init(){
        parent::init();

        $this->b=$this->join('book.author_id');

        $this->b->addField('isbn');


    }
}

class Model_BookAuthorContact extends Model_BookAuthor {
    function init(){
        parent::init();

        $this->debug();
        $this->c=$this->a->join('contact.author_id');
        $this->c->addField('address');
    }
}

