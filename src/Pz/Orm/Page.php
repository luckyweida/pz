<?php
//Last updated: 2018-09-04 20:55:50
namespace Pz\Orm;

class Page extends \Pz\Orm\Generated\Page
{
    /**
     * @return PageTemplate|null
     */
    public function objPageTempalte()
    {
        /** @var PageTemplate $pageTemplate */
        $pageTemplate = PageTemplate::getById($this->getPdo(), $this->getTemplate());
        return $pageTemplate;
    }
}