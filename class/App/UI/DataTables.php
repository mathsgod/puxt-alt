<?php

namespace App\UI;


use App\Page;

class DataTables extends Table
{

    const ATTRIBUTES = [
        "searching" => [
            "type" => "json",
            "name" => "data-searching"
        ],
        "paging" => [
            "type" => "json",
            "name" => "data-paging"
        ],
        "responsive" => [
            "type" => "json",
            "name" => "data-responsive"
        ],
        "processing" => [
            "type" => "json",
            "name" => "data-responsive"
        ],
        "dom" => [
            "name" => "data-dom"
        ],
        "pageLength" => [
            "type" => "string",
            "name" => "data-page-length"
        ]
    ] + parent::ATTRIBUTES;
    /*
    private $columns = [];
    private $objects = null;

    public $ajax = null;
    public $serverSide = false;
    public $ordering = true;
    public $scrollX = false;
    public $order = [];

    public $response = null;

    public $_order = [];
    public $select = true;
    public $autoWidth = true;


    //public $buttons = ['print', 'copy', 'excel', 'pdf'];
    public $buttons = [];

    public $fixedHeader = ["header" => false];

    public $page = null;
*/
    public function __construct($objects, Page $page)
    {
        parent::__construct($objects, $page);
        $this->setAttribute("is", "datatables");
        //$this->searching = true;
        //$this->responsive = true;
        //$this->processing = true;

        //        $this->dom = "<'row'<'col-sm-12'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-7'p><'col-sm-5'i>><'row'<'col-sm-6'B><'col-sm-6'l>>";

        $this->dom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" .
            "<'row'<'col-sm-12'tr>>" .
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
        $this->objects = $objects;
    }

    public function cardStyle()
    {
        $this->dom = "<'card'<'card-body p-1'<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" .
            "<'row'<'col-sm-12'tr>>>" .
            "<'card-footer'<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>>>";
        return $this;
    }
}
