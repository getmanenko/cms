<?php
/**
 * Created by PhpStorm.
 * User: p.onysko
 * Date: 03.03.14
 * Time: 13:46
 */
namespace samson\cms\web;


use samson\activerecord\dbRelation;

class CMSNav extends \samson\cms\CMSNav
{
    /**
     * создание новой структуры
     */
    public static function add($post)
    {
        $newNav = new CMSNav();
        foreach ($post as $key => $val) {
            $newNav[$key]=$val;
        }
        $newNav->save();
    }

    /**
     * @param $post - $_POST массив с новыми значениями для структуры
     */
    public function update($post)
    {
        foreach ($post as $key => $val) {
            $this[$key]=$val;
        }
        $this->save();
    }

    public static function fullTree(CMSNav & $parent = null)
    {
        $html = '';
        $newNavs = array();

        if (!isset($parent)) {
            $parent = new CMSNav( false );
            $parent->Name = 'Корень навигации';
            $parent->Url = 'NAVIGATION_BASE';
            $parent->StructureID = 0;
            $parent->base = 1;
            $db_navs = null;
            if(dbQuery('samson\cms\web\cmsnav')->Active(1)->join('parents_relations')->cond('parents_relations.parent_id', '', dbRelation::ISNULL)->exec($db_navs) ){
                foreach($db_navs as $db_nav){
                    $parent->children['id_'.$db_nav->id] = $db_nav;
                }
            }
        }
        /*$cmsnavs = dbQuery('\samson\cms\web\CMSNav')->cond('Active',1)
            ->order_by('PriorityNumber','asc')->exec();

        foreach ($cmsnavs as $cmsnav) {
            $newNavs[$cmsnav->Url] = $cmsnav;
        }
        self::build($parent, $newNavs);*/
        //elapsed('startHtmlTree');
        $htmlTree = $parent->htmlTree($parent, $html, 'tree.element.tmpl.php');
        //elapsed('endHtmlTree');
        return $htmlTree;
    }

    public function htmlTree(CMSNav & $parent = NULL, & $html = '', $view = NULL, $level = 0 )
    {
        if (!isset($parent)) {
           $parent = & $this;
        }
        if ($parent->base){
            $children = $parent->children();
        } else {
            $children = $parent->baseChildren();
        }
        //trace($parent->Name);
        //$children = $parent->children();
        //trace(sizeof($children).' - '.$level);
        if (sizeof($children)) {
            $html .= '<ul>';
            foreach ($children as $id => $child) {
                if (isset($view)) {
                    $html .= '<li>'
                        .m()->view($view)->db_structure($child)->output().'';
                } else {
                    $html .= '<li>'.$child->Name.'</li>';
                }
                //if ($level < 5)
                    $parent->htmlTree($child, $html, $view, $level++);
                $html .= '</li>';
            }
            $html .='</ul>';
        }

        return $html;
    }
}