<?php

/**
 * admin contents list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_contents_list()
{
	$output = Redaxscript\Hook::trigger('adminContentListStart');

	/* define access variables */

	$table_new = TABLE_NEW;
	if (TABLE_PARAMETER == 'comments')
	{
		$articles_total = Redaxscript\Db::forTablePrefix('articles')->count();
		$articles_comments_disable = Redaxscript\Db::forTablePrefix('articles')->where('comments', 0)->count();
		if ($articles_total == $articles_comments_disable)
		{
			$table_new = 0;
		}
	}

	/* switch table */

	switch (TABLE_PARAMETER)
	{
		case 'categories':
			$wording_single = 'category';
			$wording_parent = 'category_parent';
			break;
		case 'articles':
			$wording_single = 'article';
			$wording_parent = 'category';
			break;
		case 'extras':
			$wording_single = 'extra';
			break;
		case 'comments':
			$wording_single = 'comment';
			$wording_parent = 'article';
			break;
	}

	/* query contents */

	$result = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->orderByAsc('rank')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="rs-admin-title-content">' . l(TABLE_PARAMETER) . '</h2>';
	$output .= '<div class="rs-admin-wrapper-button">';
	if ($table_new == 1)
	{
		$output .= anchor_element('internal', '', 'rs-admin-button-default rs-admin-button-plus', l($wording_single . '_new'), 'admin/new/' . TABLE_PARAMETER);
	}
	if (TABLE_EDIT == 1 && $num_rows)
	{
		$output .= anchor_element('internal', '', 'rs-admin-button-default rs-admin-button-sort', l('sort'), 'admin/sort/' . TABLE_PARAMETER . '/' . TOKEN);
	}
	$output .= '</div><div class="rs-admin-wrapper-table"><table class="rs-admin-table">';

	/* collect thead */

	$output .= '<thead><tr><th class="rs-admin-s3o6 rs-admin-column-first">' . l('title') . '</th><th class="';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= 'rs-admin-s1o6';
	}
	else
	{
		$output .= 'rs-admin-s3o6';
	}
	$output .= ' rs-admin-column-second">';
	if (TABLE_PARAMETER == 'comments')
	{
		$output .= l('identifier');
	}
	else
	{
		$output .= l('alias');
	}
	$output .= '</th>';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= '<th class="rs-admin-column-third">' . l($wording_parent) . '</th>';
	}
	$output .= '<th class="rs-admin-column-move rs-admin-column-last">' . l('rank') . '</th></tr></thead>';

	/* collect tfoot */

	$output .= '<tfoot><tr><td class="rs-admin-column-first">' . l('title') . '</td><td class="rs-admin-column-second">';
	if (TABLE_PARAMETER == 'comments')
	{
		$output .= l('identifier');
	}
	else
	{
		$output .= l('alias');
	}
	$output .= '</td>';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= '<td class="rs-admin-column-third">' . l($wording_parent) . '</td>';
	}
	$output .= '<td class="rs-admin-column-move rs-admin-column-last">' . l('rank') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l($wording_single . '_no') . l('point');
	}
	else if ($result)
	{
		$accessValidator = new Redaxscript\Validator\Access();
		foreach ($result as $r)
		{
			$access = $r['access'];

			/* access granted */

			if ($accessValidator->validate($access, MY_GROUPS) === Redaxscript\Validator\ValidatorInterface::PASSED)
			{
				if ($r)
				{
					foreach ($r as $key => $value)
					{
						$$key = stripslashes($value);
					}
				}

				/* prepare name */

				if (TABLE_PARAMETER == 'comments')
				{
					$name = $author . l('colon') . ' ' . strip_tags($text);
				}
				else
				{
					$name = $title;
				}

				/* build class string */

				if ($status == 1)
				{
					$class_status = '';
				}
				else
				{
					$class_status = 'row_disabled';
				}

				/* build route */

				if (TABLE_PARAMETER != 'extras' && $status == 1)
				{
					if (TABLE_PARAMETER == 'categories' && $parent == 0 || TABLE_PARAMETER == 'articles' && $category == 0)
					{
						$route = $alias;
					}
					else
					{
						$route = build_route(TABLE_PARAMETER, $id);
					}
				}
				else
				{
					$route = '';
				}

				/* collect tbody output */

				if (TABLE_PARAMETER == 'categories')
				{
					if ($before != $parent)
					{
						$output .= '<tbody><tr class="rs-row-group"><td colspan="4">';
						if ($parent)
						{
							$output .= Redaxscript\Db::forTablePrefix('categories')->where('id', $parent)->findOne()->title;
						}
						else
						{
							$output .= l('none');
						}
						$output .= '</td></tr>';
					}
					$before = $parent;
				}
				if (TABLE_PARAMETER == 'articles')
				{
					if ($before != $category)
					{
						$output .= '<tbody><tr class="rs-row-group"><td colspan="4">';
						if ($category)
						{
							$output .= Redaxscript\Db::forTablePrefix('categories')->where('id', $category)->findOne()->title;
						}
						else
						{
							$output .= l('uncategorized');
						}
						$output .= '</td></tr>';
					}
					$before = $category;
				}
				if (TABLE_PARAMETER == 'comments')
				{
					if ($before != $article)
					{
						$output .= '<tbody><tr class="rs-row-group"><td colspan="4">';
						if ($article)
						{
							$output .= Redaxscript\Db::forTablePrefix('articles')->where('id', $article)->findOne()->title;
						}
						else
						{
							$output .= l('none');
						}
						$output .= '</td></tr>';
					}
					$before = $article;
				}

				/* collect table row */

				$output .= '<tr';
				if ($alias)
				{
					$output .= ' id="' . $alias . '"';
				}
				if ($class_status)
				{
					$output .= ' class="' . $class_status . '"';
				}
				$output .= '><td class="rs-admin-column-first">';
				if ($language)
				{
					$output .= '<span class="rs-admin-icon-flag rs-admin-language-' . $language . '" title="' . $language . '">' . $language . '</span>';
				}
				if ($status == 1)
				{
					$output .= anchor_element('internal', '', 'rs-admin-link-default', $name, $route);
				}
				else
				{
					$output .= $name;
				}

				/* collect control output */

				$output .= admin_control('contents', TABLE_PARAMETER, $id, $alias, $status, TABLE_NEW, TABLE_EDIT, TABLE_DELETE);

				/* collect alias and id output */

				$output .= '</td><td class="rs-admin-column-second">';
				if (TABLE_PARAMETER == 'comments')
				{
					$output .= $id;
				}
				else
				{
					$output .= $alias;
				}
				$output .= '</td>';

				/* collect parent output */

				if (TABLE_PARAMETER != 'extras')
				{
					$output .= '<td class="rs-admin-column-third">';
					if (TABLE_PARAMETER == 'categories')
					{
						if ($parent)
						{
							$parent_title = Redaxscript\Db::forTablePrefix('categories')->where('id', $parent)->findOne()->title;
							$output .= anchor_element('internal', '', 'rs-admin-link-default', $parent_title, 'admin/edit/categories/' . $parent);
						}
						else
						{
							$output .= l('none');
						}
					}
					if (TABLE_PARAMETER == 'articles')
					{
						if ($category)
						{
							$category_title = Redaxscript\Db::forTablePrefix('categories')->where('id', $category)->findOne()->title;
							$output .= anchor_element('internal', '', 'rs-admin-link-default', $category_title, 'admin/edit/categories/' . $category);
						}
						else
						{
							$output .= l('uncategorized');
						}
					}
					if (TABLE_PARAMETER == 'comments')
					{
						if ($article)
						{
							$article_title = Redaxscript\Db::forTablePrefix('articles')->where('id', $article)->findOne()->title;
							$output .= anchor_element('internal', '', 'rs-admin-link-default', $article_title, 'admin/edit/articles/' . $article);
						}
						else
						{
							$output .= l('none');
						}
					}
					$output .= '</td>';
				}
				$output .= '<td class="rs-admin-column-move rs-admin-column-last">';

				/* collect control output */

				if (TABLE_EDIT == 1)
				{
					$rank_desc = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->max('rank');
					if ($rank > 1)
					{
						$output .= anchor_element('internal', '', 'rs-admin-move-up', l('up'), 'admin/up/' . TABLE_PARAMETER . '/' . $id . '/' . TOKEN);
					}
					else
					{
						$output .= '<span class="rs-admin-move-up">' . l('up') . '</span>';
					}
					if ($rank < $rank_desc)
					{
						$output .= anchor_element('internal', '', 'rs-admin-move-down', l('down'), 'admin/down/' . TABLE_PARAMETER . '/' . $id . '/' . TOKEN);
					}
					else
					{
						$output .= '<span class="rs-admin-move-down">' . l('down') . '</span>';
					}
					$output .= '</td>';
				}
				$output .= '</tr>';

				/* collect tbody output */

				if (TABLE_PARAMETER == 'categories')
				{
					if ($before != $parent)
					{
						$output .= '</tbody>';
					}
				}
				if (TABLE_PARAMETER == 'articles')
				{
					if ($before != $category)
					{
						$output .= '</tbody>';
					}
				}
				if (TABLE_PARAMETER == 'comments')
				{
					if ($before != $article)
					{
						$output .= '</tbody>';
					}
				}
			}
			else
			{
				$counter++;
			}
		}

		/* handle access */

		if ($num_rows == $counter)
		{
			$error = l('access_no') . l('point');
		}
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="4">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminContentListEnd');
	echo $output;
}

/**
 * admin groups list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_groups_list()
{
	$output = Redaxscript\Hook::trigger('adminGroupListStart');

	/* query groups */

	$result = Redaxscript\Db::forTablePrefix('groups')->orderByAsc('name')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="rs-admin-title-content">' . l('groups') . '</h2>';
	$output .= '<div class="rs-admin-wrapper-button">';
	if (GROUPS_NEW == 1)
	{
		$output .= anchor_element('internal', '', 'rs-admin-button-default rs-admin-button-plus', l('group_new'), 'admin/new/groups');
	}
	$output .= '</div><div class="rs-admin-wrapper-table"><table class="rs-admin-table">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="rs-admin-s4o6 rs-admin-column-first">' . l('name') . '</th><th class="rs-admin-s1o6 rs-admin-column-second">' . l('alias') . '</th><th class="rs-admin-s1o6 rs-admin-column-last">' . l('filter') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="rs-admin-column-first">' . l('name') . '</td><td class="rs-admin-column-second">' . l('alias') . '</td><td class="rs-admin-column-last">' . l('filter') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l('group_no') . l('point');
	}
	else if ($result)
	{
		$output .= '<tbody>';
		foreach ($result as $r)
		{
			if ($r)
			{
				foreach ($r as $key => $value)
				{
					$$key = stripslashes($value);
				}
			}

			/* build class string */

			if ($status == 1)
			{
				$class_status = '';
			}
			else
			{
				$class_status = 'row_disabled';
			}

			/* collect table row */

			$output .= '<tr';
			if ($alias)
			{
				$output .= ' id="' . $alias . '"';
			}
			if ($class_status)
			{
				$output .= ' class="' . $class_status . '"';
			}
			$output .= '><td class="rs-admin-column-first">' . $name;

			/* collect control output */

			$output .= admin_control('access', 'groups', $id, $alias, $status, GROUPS_NEW, GROUPS_EDIT, GROUPS_DELETE);

			/* collect alias and filter output */

			$output .= '</td><td class="rs-admin-column-second">' . $alias . '</td><td class="rs-admin-column-last">' . $filter . '</td></tr>';
		}
		$output .= '</tbody>';
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="3">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminGroupListEnd');
	echo $output;
}

/**
 * admin users list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_users_list()
{
	$output = Redaxscript\Hook::trigger('adminUserListStart');

	/* query users */

	$result = Redaxscript\Db::forTablePrefix('users')->orderByDesc('last')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="rs-admin-title-content">' . l('users') . '</h2>';
	$output .= '<div class="rs-admin-wrapper-button">';
	if (USERS_NEW == 1)
	{
		$output .= anchor_element('internal', '', 'rs-admin-button-default rs-admin-button-plus', l('user_new'), 'admin/new/users');
	}
	$output .= '</div><div class="rs-admin-wrapper-table"><table class="rs-admin-table">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="rs-admin-s3o6 rs-admin-column-first">' . l('name') . '</th><th class="rs-admin-s1o6 rs-admin-column-second">' . l('user') . '</th><th class="rs-admin-s1o6 rs-admin-column-third">' . l('groups') . '</th><th class="rs-admin-s1o6 rs-admin-column-last">' . l('session') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="rs-admin-column-first">' . l('name') . '</td><td class="rs-admin-column-second">' . l('user') . '</td><td class="rs-admin-column-third">' . l('groups') . '</td><td class="rs-admin-column-last">' . l('session') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l('user_no') . l('point');
	}
	else if ($result)
	{
		$output .= '<tbody>';
		foreach ($result as $r)
		{
			if ($r)
			{
				foreach ($r as $key => $value)
				{
					$$key = stripslashes($value);
				}
			}

			/* build class string */

			if ($status == 1)
			{
				$class_status = '';
			}
			else
			{
				$class_status = 'row_disabled';
			}

			/* collect table row */

			$output .= '<tr';
			if ($user)
			{
				$output .= ' id="' . $user . '"';
			}
			if ($class_status)
			{
				$output .= ' class="' . $class_status . '"';
			}
			$output .= '><td class="rs-admin-column-first">';
			if ($language)
			{
				$output .= '<span class="rs-admin-icon-flag rs-admin-language-' . $language . '" title="' . l($language) . '">' . $language . '</span>';
			}
			$output .= $name;

			/* collect control output */

			$output .= admin_control('access', 'users', $id, $alias, $status, USERS_NEW, USERS_EDIT, USERS_DELETE);

			/* collect user and parent output */

			$output .= '</td><td class="rs-admin-column-second">' . $user . '</td><td class="rs-admin-column-third">';
			if ($groups)
			{
				$groups_array = explode(', ', $groups);
				$groups_array_keys = array_keys($groups_array);
				$groups_array_last = end($groups_array_keys);
				foreach ($groups_array as $key => $value)
				{
					$group_alias = Redaxscript\Db::forTablePrefix('groups')->where('id', $value)->findOne()->alias;
					if ($group_alias)
					{
						$group_name = Redaxscript\Db::forTablePrefix('groups')->where('id', $value)->findOne()->name;
						$output .= anchor_element('internal', '', 'rs-admin-link-default', $group_name, 'admin/edit/groups/' . $value);
						if ($groups_array_last != $key)
						{
							$output .= ', ';
						}
					}
				}
			}
			else
			{
				$output .= l('none');
			}
			$output .= '</td><td class="rs-admin-column-last">';
			if ($first == $last)
			{
				$output .= l('none');
			}
			else
			{
				$minute_ago = date('Y-m-d H:i:s', strtotime('-1 minute'));
				$day_ago = date('Y-m-d H:i:s', strtotime('-1 day'));
				if ($last > $minute_ago)
				{
					$output .= l('online');
				}
				else if ($last > $day_ago)
				{
					$time = date(s('time'), strtotime($last));
					$output .= l('today') . ' ' . l('at') . ' ' . $time;
				}
				else
				{
					$date = date(s('date'), strtotime($last));
					$output .= $date;
				}
			}
			$output .= '</td></tr>';
		}
		$output .= '</tbody>';
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="3">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminUserListEnd');
	echo $output;
}

/**
 * admin modules list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_modules_list()
{
	$output = Redaxscript\Hook::trigger('adminModuleListStart');

	/* query modules */

	$result = Redaxscript\Db::forTablePrefix('modules')->orderByAsc('name')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="rs-admin-title-content">' . l('modules') . '</h2>';
	$output .= '<div class="rs-admin-wrapper-table"><table class="rs-admin-table">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="rs-admin-s4o6 rs-admin-column-first">' . l('name') . '</th><th class="rs-admin-s1o6 rs-admin-column-second">' . l('alias') . '</th><th class="rs-admin-s1o6 rs-admin-column-last">' . l('version') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="rs-admin-column-first">' . l('name') . '</td><td class="rs-admin-column-second">' . l('alias') . '</td><td class="rs-admin-column-last">' . l('version') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l('module_no') . l('point');
	}
	else if ($result)
	{
		$accessValidator = new Redaxscript\Validator\Access();
		$output .= '<tbody>';
		foreach ($result as $r)
		{
			$access = $r['access'];

			/* access granted */

			if ($accessValidator->validate($access, MY_GROUPS) === Redaxscript\Validator\ValidatorInterface::PASSED)
			{
				if ($r)
				{
					foreach ($r as $key => $value)
					{
						$$key = stripslashes($value);
					}
				}
				$modules_installed_array[] = $alias;

				/* build class string */

				if ($status == 1)
				{
					$class_status = '';
				}
				else
				{
					$class_status = 'row_disabled';
				}

				/* collect table row */

				$output .= '<tr';
				if ($alias)
				{
					$output .= ' id="' . $alias . '"';
				}
				if ($class_status)
				{
					$output .= ' class="' . $class_status . '"';
				}
				$output .= '><td class="column-first">' . $name;

				/* collect control output */

				$output .= admin_control('modules_installed', 'modules', $id, $alias, $status, MODULES_INSTALL, MODULES_EDIT, MODULES_UNINSTALL);

				/* collect alias and version output */

				$output .= '</td><td class="column-second">' . $alias . '</td><td class="column-last">' . $version . '</td></tr>';
			}
			else
			{
				$counter++;
			}
		}
		$output .= '</tbody>';

		/* handle access */

		if ($num_rows == $counter)
		{
			$error = l('access_no') . l('point');
		}
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="3">' . $error . '</td></tr></tbody>';
	}

	/* modules not installed */

	if (MODULES_INSTALL == 1)
	{
		/* modules directory object */

		$modules_directory = new Redaxscript\Directory();
		$modules_directory->init('modules');
		$modules_directory_array = $modules_directory->getArray();
		if ($modules_directory_array && $modules_installed_array)
		{
			$modules_not_installed_array = array_diff($modules_directory_array, $modules_installed_array);
		}
		else if ($modules_directory_array)
		{
			$modules_not_installed_array = $modules_directory_array;
		}
		if ($modules_not_installed_array)
		{
			$output .= '<tbody><tr class="rs-row-group"><td colspan="3">' . l('install') . '</td></tr>';
			foreach ($modules_not_installed_array as $alias)
			{
				/* collect table row */

				$output .= '<tr';
				if ($alias)
				{
					$output .= ' id="' . $alias . '"';
				}
				$output .= '><td colspan="3">' . $alias;

				/* collect control output */

				$output .= admin_control('modules_not_installed', 'modules', $id, $alias, $status, MODULES_INSTALL, MODULES_EDIT, MODULES_UNINSTALL);
				$output .= '</td></tr>';
			}
			$output .= '</tbody>';
		}
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminModuleListEnd');
	echo $output;
}