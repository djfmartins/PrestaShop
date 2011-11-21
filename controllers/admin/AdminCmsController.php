<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7300 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminCmsControllerCore extends AdminController
{
	private $_category;

	public $id_cms_category;

	public function __construct()
	{
	 	$this->table = 'cms';
	 	$this->className = 'CMS';
	 	$this->lang = true;
		$this->addRowAction('view');
		$this->addRowAction('edit');
		$this->addRowAction('delete');
	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fieldsDisplay = array(
			'id_cms' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'link_rewrite' => array('title' => $this->l('URL'), 'width' => 'auto'),
			'meta_title' => array('title' => $this->l('Title'), 'width' => '300', 'filter_key' => 'b!meta_title'),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
			'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
			);

		$this->_category = AdminCmsContentController::getCurrentCMSCategory();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'cms_category` c ON (c.`id_cms_category` = a.`id_cms_category`)';
		$this->_select = 'a.position ';
		$this->_filter = 'AND c.id_cms_category = '.(int)($this->_category->id);

		parent::__construct();
	}

	public function initForm()
	{
		$this->display = 'edit';
		$this->toolbar_btn['save-and-preview'] = array(
			'href' => '#',
			'desc' => $this->l('Save and preview')
		);
		$this->initToolbar();
		if (!$this->loadObject(true))
			return;

		$categories = CMSCategory::getCategories($this->context->language->id, false);
		$html_categories = CMSCategory::recurseCMSCategory($categories, $categories[0][1], 1, $this->getFieldValue($this->object, 'id_cms_category'), 1);

		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('CMS Category'),
				'image' => '../img/admin/tab-categories.gif'
			),
			'input' => array(
				// custom template
				array(
					'type' => 'select_category',
					'label' => $this->l('Parent CMS Category'),
					'name' => 'id_cms_category',
					'options' => array(
						'html' => $html_categories,
					),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta title:'),
					'name' => 'meta_title',
					'id' => 'name', // for copy2friendlyUrl compatibility
					'lang' => true,
					'required' => true,
					'class' => 'copy2friendlyUrl',
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta description'),
					'name' => 'meta_description',
					'lang' => true,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Meta keywords'),
					'name' => 'meta_keywords',
					'lang' => true,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Friendly URL'),
					'name' => 'link_rewrite',
					'required' => true,
					'lang' => true,
					'hint' => $this->l('Only letters and the minus (-) character are allowed')
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Page content'),
					'name' => 'content',
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Enable:'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shop association:'),
				'name' => 'checkBoxShopAsso',
				'values' => Shop::getTree()
			);
		}

		$this->tpl_form_vars = array(
			'active' => $this->object->active
		);

		return parent::initForm();
	}

	public function initList()
	{
		$this->toolbar_title = $this->l('Pages in this category');
		$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&amp;add'.$this->table.'&amp;id_cms_category='.(int)$this->id_cms_category.'&amp;token='.$this->token,
			'desc' => $this->l('Add new')
		);

		return parent::initList();
	}

	public function displayList($token = NULL)
	{
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}

	/**
	 * Modifying initial getList method to display position feature (drag and drop)
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		if ($order_by && $this->context->cookie->__get($this->table.'Orderby'))
			$order_by = $this->context->cookie->__get($this->table.'Orderby');
		else
			$order_by = 'position';

		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
	}

	function postProcess()
	{
		if (Tools::isSubmit('viewcms') AND ($id_cms = (int)(Tools::getValue('id_cms'))) AND $cms = new CMS($id_cms, $this->context->language->id) AND Validate::isLoadedObject($cms))
		{
			$redir = $this->context->link->getCMSLink($cms);
			if (!$cms->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
				$redir .= '?adtoken='.Tools::encrypt('PreviewCMS'.$cms->id).'&ad='.$admin_dir;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deletecms'))
		{
			if (Tools::getValue('id_cms') == Configuration::get('PS_CONDITIONS_CMS_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_CMS_ID', 0);
			}
			$cms = new CMS((int)(Tools::getValue('id_cms')));
			$cms->cleanPositions($cms->id_cms_category);
			if (!$cms->delete())
				$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
			else
				Tools::redirectAdmin(self::$currentIndex.'&id_cms_category='.$cms->id_cms_category.'&conf=1&token='.Tools::getAdminTokenLite('AdminCmsContent'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$cms = new CMS();
					$result = true;
					$result = $cms->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$cms->cleanPositions((int)(Tools::getValue('id_cms_category')));
						Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.Tools::getAdminTokenLite('AdminCmsContent').'&id_category='.(int)(Tools::getValue('id_cms_category')));
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddcms') OR Tools::isSubmit('submitAddcmsAndPreview'))
		{
			parent::validateRules();
			if (!sizeof($this->_errors))
			{
				if (!$id_cms = (int)(Tools::getValue('id_cms')))
				{
					$cms = new CMS();
					$this->copyFromPost($cms, 'cms');
					if (!$cms->add())
						$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($cms->id);
					if (Tools::isSubmit('submitAddcmsAndPreview'))
					{
						$preview_url = $this->context->link->getCMSLink($cms, $this->getFieldValue($cms, 'link_rewrite', $this->context->language->id), $this->context->language->id);

						if (!$cms->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewCMS'.$cms->id);

							$preview_url .= $cms->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
						Tools::redirectAdmin(self::$currentIndex.'&id_cms_category='.$cms->id_cms_category.'&conf=3&token='.Tools::getAdminTokenLite('AdminCmsContent'));
				}
				else
				{
					$cms = new CMS($id_cms);
					$this->copyFromPost($cms, 'cms');
					if (!$cms->update())
						$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.' ('.Db::getInstance()->getMsgError().')</b>';
					else
						$this->updateAssoShop($cms->id);
					if (Tools::isSubmit('submitAddcmsAndPreview'))
					{
						$preview_url = $this->context->link->getCMSLink($cms, $this->getFieldValue($object, 'link_rewrite', $this->context->language->id), $this->context->language->id);
						if (!$cms->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewCMS'.$cms->id);

							$preview_url .= $object->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
						Tools::redirectAdmin(self::$currentIndex.'&id_cms_category='.$cms->id_cms_category.'&conf=4&token='.Tools::getAdminTokenLite('AdminCmsContent'));
				}
			}
		}
		elseif (Tools::getValue('position'))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = $this->loadObject()))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin(self::$currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_cms_category'))) ? ('&id_cms_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminCmsContent'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('status') AND Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin(self::$currentIndex.'&conf=5'.((int)Tools::getValue('id_cms_category') ? '&id_cms_category='.(int)Tools::getValue('id_cms_category') : '').'&token='.Tools::getValue('token'));
					else
						$this->_errors[] = Tools::displayError('An error occurred while updating status.');
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		else
			parent::postProcess(true);
	}
}


