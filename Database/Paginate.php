<?php

namespace khokonc\mvc\Database;

class Paginate
{
    protected $total;
    protected $perPage;
    protected $currentPage;
    protected $path;
    public $data;

    public function __construct(array $paginate)
    {
        $this->total = $paginate['aggregate'];
        $this->perPage = $paginate['limit'];
        $this->path = $paginate['path'];
        $this->data  = $paginate['data'];
        if (empty($paginate['currentPage'])) :
            $this->currentPage = 1;
        else :
            $this->currentPage = intval($paginate['currentPage']);
        endif;
    }


    public function links()
    {
        return view('vendor.paginate', [
            'paginator' => $this,
            'elements' => $this->elements()
        ]);
    }


    public function firstItem()
    {

        $offset = ($this->currentPage - 1) * $this->perPage;
        if(count($this->data)==0){
            return 0;
        }
        return $offset + 1;
    }

    public function lastItem()
    {
        $offset = ($this->currentPage - 1) * $this->perPage;
        return $offset + count($this->data);
    }

    public function total()
    {
        return $this->total;
    }

   

    public function elements()
    {
        $totalPage = ceil($this->total / $this->perPage);

        $elements = [];
        $loopIteration = 1;
        $offset = ($this->currentPage()-1) * $this->perPage;

        $page = $this->currentPage() > 7 ? 6 : 1;

        for (; $page <= $totalPage; $page++) {
            $elements[$page] = $this->path . "?page=$page";
            if ($loopIteration == 7 && $this->total > 10) :
                $page = $page+3;
                $elements['...'] = $this->path."?page=$page";
                break;
            endif;
            $loopIteration ++;
        }


        return $elements;
    }

    public function onFirstPage()
    {
        return $this->currentPage == 1;
    }

    public function currentPage()
    {
        return $this->currentPage;
    }


    public function previousPageUrl()
    {
        $prev = $this->currentPage - 1;
        return $this->path . "?page=$prev";
    }


    public function nextPageUrl()
    {
        $next = $this->currentPage + 1;
        $next = $next > ceil($this->total / $this->perPage) ? $this->currentPage() : $next;
        return $this->path . "?page=$next";
    }


    public function hasMorePage()
    {
        $lastPage = ceil($this->total / $this->perPage);
        return $lastPage > $this->currentPage;
    }
}
