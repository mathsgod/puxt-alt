<?php

namespace App\UI;

use Psr\Http\Message\RequestInterface;

class RTRequest
{
    private $request;
    private $query = [];

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $this->query);
    }

    //Queryable
    public function setDataSource($source)
    {
        $this->source = $source;
    }

    public function filteredSource()
    {
        $source = clone $this->source;

        foreach ($this->query["filter"] as $filter) {
            $name = $filter["name"];
            if (isset($this->search_callback[$name])) {
                $r = call_user_func($this->search_callback[$name], $filter["value"]);
                $source->where($r[0], $r[1]);
                continue;
            }

            switch ($filter["method"]) {
                case "like":
                    $source->where("$name like :$name", [$name => "%" . $filter["value"] . "%"]);
                    break;
                case "equal":
                    $source->filter([$name => $filter["value"]]);
                    break;
                case "date":
                    $value = $filter["value"];
                    $from = $value[0];
                    $to = $value[1];
                    if ($from == $to) {
                        $source->where("date(`$name`) = :$name", [$name => $from]);
                    } else {
                        $field_from = ":" . $name . "_from";
                        $field_to = ":" . $name . "_to";
                        $source->where("date(`$name`) between $field_from and $field_to", [
                            $field_from => $from,
                            $field_to => $to
                        ]);
                    }
                    break;
            }
        }
        return $source;
    }

    public function data()
    {

        return $this->filteredSource();
    }

    public function __debugInfo()
    {
        return ["query" => $this->query];
    }

    public function setSearchCallback(string $name, callable $callable)
    {
        $this->search_callback[$name] = $callable;
    }
}
