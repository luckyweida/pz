<?php

namespace Pz\Controller;


use Doctrine\DBAL\Connection;
use Pz\Axiom\Mo;
use Pz\Orm\_Model;
use Pz\Orm\AssetSize;
use Pz\Orm\DataGroup;
use Pz\Orm\FragmentBlock;
use Pz\Orm\FragmentDefault;
use Pz\Orm\FragmentTag;
use Pz\Orm\Page;
use Pz\Orm\PageCategory;
use Pz\Orm\PageTemplate;
use Pz\Orm\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class Init extends Controller
{

    /**
     * @route("/init_pz", name="init")
     * @return Response
     */
    public function init()
    {
        $connection = $this->container->get('doctrine.dbal.default_connection');
        /** @var \PDO $pdo */
        $pdo = $connection->getWrappedConnection();

        $pdo->beginTransaction();

        $dir = $this->container->getParameter('kernel.project_dir') . '/vendor/pozoltd/pz/src/Pz/Orm';
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated') {
                continue;
            }

            $className = "Pz\\Orm\\" . substr($file, 0, strrpos($file, '.'));
            $className::sync($pdo);
        }
        foreach ($files as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'Generated') {
                continue;
            }

            $className = "Pz\\Orm\\" . substr($file, 0, strrpos($file, '.'));
            $className::updateModel($pdo);
        }

        $pdo->commit();


        $pdo->beginTransaction();

        $this->addUsers($pdo);
        $this->addAssetSizes($pdo);
        $this->addPageCategories($pdo);
        $this->addPageTemplates($pdo);
        $this->addPages($pdo);
        $this->addFragmentTags($pdo);
        $this->addFragmentBlock($pdo);
        $this->addFragmentDefault($pdo);
        $this->addDataGroups($pdo);

        $pdo->commit();

        return new Response('OK');
    }

    public function addUsers($pdo)
    {
        $orm = User::getByField($pdo, 'title', 'weida');
        if (!$orm) {
            $encoder = new MessageDigestPasswordEncoder();
            $orm = new User($pdo);
            $orm->setId(1);
            $orm->setTitle('weida');
            $orm->setPassword($encoder->encodePassword('123', ''));
            $orm->setName('Weida');
            $orm->setEmail('luckyweida@gmail.com');
            $orm->save(true);
        }

        $orm = User::getByField($pdo, 'title', 'admin');
        if (!$orm) {
            $encoder = new MessageDigestPasswordEncoder();
            $orm = new User($pdo);
            $orm->setId(2);
            $orm->setTitle('admin');
            $orm->setPassword($encoder->encodePassword('dasiyebushuo', ''));
            $orm->setName('Administrator');
            $orm->setEmail('pozoltd@gmail.com');
            $orm->save(true);
        }
    }

    public function addAssetSizes($pdo)
    {
        $orm = AssetSize::getByField($pdo, 'title', 'full');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setId(1);
            $orm->setTitle('full');
            $orm->setWidth(1440);
            $orm->save(true);
        }

        $orm = AssetSize::getByField($pdo, 'title', 'large');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setId(2);
            $orm->setTitle('large');
            $orm->setWidth(960);
            $orm->save(true);
        }

        $orm = AssetSize::getByField($pdo, 'title', 'medium');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setId(3);
            $orm->setTitle('medium');
            $orm->setWidth(480);
            $orm->save(true);
        }

        $orm = AssetSize::getByField($pdo, 'title', 'small');
        if (!$orm) {
            $orm = new AssetSize($pdo);
            $orm->setId(4);
            $orm->setTitle('small');
            $orm->setWidth(240);
            $orm->save(true);
        }
    }

    public function addPageCategories($pdo)
    {
        $orm = PageCategory::getByField($pdo, 'title', 'Main nav');
        if (!$orm) {
            $orm = new PageCategory($pdo);
            $orm->setId(1);
            $orm->setTitle('Main nav');
            $orm->setCode('main');
            $orm->save(true);
        }

        $orm = PageCategory::getByField($pdo, 'title', 'Footer nav');
        if (!$orm) {
            $orm = new PageCategory($pdo);
            $orm->setId(2);
            $orm->setTitle('Footer nav');
            $orm->setCode('footer');
            $orm->save(true);
        }
    }

    public function addPageTemplates($pdo)
    {
        $orm = PageTemplate::getByField($pdo, 'title', 'layout.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(1);
            $orm->setTitle('layout.html.twig');
            $orm->setFilename('layout.html.twig');
            $orm->save(true);
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'home.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(2);
            $orm->setTitle('home.html.twig');
            $orm->setFilename('home.html.twig');
            $orm->save(true);
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'about.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(3);
            $orm->setTitle('about.html.twig');
            $orm->setFilename('about.html.twig');
            $orm->save(true);
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'news.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(4);
            $orm->setTitle('news.html.twig');
            $orm->setFilename('news.html.twig');
            $orm->save(true);
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'news-detail.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(5);
            $orm->setTitle('news-detail.html.twig');
            $orm->setFilename('news-detail.html.twig');
            $orm->save(true);
        }

        $orm = PageTemplate::getByField($pdo, 'title', 'contact.html.twig');
        if (!$orm) {
            $orm = new PageTemplate($pdo);
            $orm->setId(6);
            $orm->setTitle('contact.html.twig');
            $orm->setFilename('contact.html.twig');
            $orm->save(true);
        }
    }

    public function addPages($pdo)
    {
        $orm = Page::getByField($pdo, 'title', 'Home');
        if (!$orm) {
            $orm = new Page($pdo);
            $orm->setId(1);
            $orm->setTitle('Home');
            $orm->setType(1);
            $orm->setTemplate(2);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/');
            $orm->setCategoryRank(json_encode(array("cat1" => 0)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);
        }

        $orm = Page::getByField($pdo, 'title', 'About');
        if (!$orm) {
            $orm = new Page($pdo);
            $orm->setId(2);
            $orm->setTitle('About');
            $orm->setType(1);
            $orm->setTemplate(3);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/about');
            $orm->setCategoryRank(json_encode(array("cat1" => 1)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);
        }

        $orm = Page::getByField($pdo, 'title', 'News');
        if (!$orm) {
            $orm = new Page($pdo);
            $orm->setId(3);
            $orm->setTitle('News');
            $orm->setType(1);
            $orm->setTemplate(4);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/news');
            $orm->setCategoryRank(json_encode(array("cat1" => 2)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);
        }

        $orm = Page::getByField($pdo, 'title', 'News detail');
        if (!$orm) {
            $orm = new Page($pdo);
            $orm->setId(4);
            $orm->setTitle('News detail');
            $orm->setType(1);
            $orm->setTemplate(5);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/news/detail');
            $orm->setCategoryRank(json_encode(array("cat1" => 0)));
            $orm->setCategoryParent(json_encode(array("cat1" => 3)));
            $orm->setStatus(2);
            $orm->save(true);
        }

        $orm = Page::getByField($pdo, 'title', 'Contact');
        if (!$orm) {
            $orm = new Page($pdo);
            $orm->setId(5);
            $orm->setTitle('Contact');
            $orm->setType(1);
            $orm->setTemplate(6);
            $orm->setCategory(json_encode(array("1")));
            $orm->setUrl('/contact');
            $orm->setCategoryRank(json_encode(array("cat1" => 3)));
            $orm->setCategoryParent(json_encode(array("cat1" => 0)));
            $orm->save(true);
        }
    }

    public function addFragmentTags($pdo)
    {
        $orm = FragmentTag::getByField($pdo, 'title', 'Page');
        if (!$orm) {
            $orm = new FragmentTag($pdo);
            $orm->setId(1);
            $orm->setTitle('Page');
            $orm->save(true);
        }

        $orm = FragmentTag::getByField($pdo, 'title', 'CMS');
        if (!$orm) {
            $orm = new FragmentTag($pdo);
            $orm->setId(2);
            $orm->setTitle('CMS');
            $orm->save(true);
        }
    }

    public function addFragmentBlock($pdo)
    {
        $orm = FragmentBlock::getByField($pdo, 'title', 'Heading & Content');
        if (!$orm) {
            $orm = new FragmentBlock($pdo);
            $orm->setId(1);
            $orm->setTitle('Heading & Content');
            $orm->setTwig('heading-content.twig');
            $orm->setTags('["1"]');
            $orm->setItems('[{"widget":"0","id":"heading","title":"Heading:","sql":""},{"widget":"5","id":"content","title":"Content:","sql":""}]');
            $orm->save(true);
        }
    }

    public function addFragmentDefault($pdo)
    {
        $orm = FragmentDefault::getByField($pdo, 'title', 'Page');
        if (!$orm) {
            $orm = new FragmentDefault($pdo);
            $orm->setId(1);
            $orm->setTitle('Page');
            $orm->setAttr('content');
            $orm->setContent('[{"id":"content","title":"Content:","tags":["1"]}]');
            $orm->save(true);
        }
    }

    public function addDataGroups($pdo)
    {
        $orm = DataGroup::getByField($pdo, 'title', 'Database');
        if (!$orm) {
            $orm = new DataGroup($pdo);
            $orm->setId(1);
            $orm->setTitle('Database');
            $orm->setIcon('fa fa-database');
            $orm->save(true);
        }

        $orm = DataGroup::getByField($pdo, 'title', 'Shop');
        if (!$orm) {
            $orm = new DataGroup($pdo);
            $orm->setId(2);
            $orm->setTitle('Shop');
            $orm->setIcon('fa fa-shopping-cart');
            $orm->save(true);
        }
    }
}