<?php

/* * **********************************************************************************************\
 * powered by aqsmoke
 * 2012.1.8
 * 增删改查，操作的主要函数文件
 * 目前拥有搜索功能
  \*********************************************************************************************** */

if (!IN_AQADMIN) {
    exit();
}

function index() {
    global $tablename, $aqConfig, $fieldTitle, $viewField, $tableTitle, $mainId, $perpage, $db, $G, $P, $searchField, $order, $picField;
    // can do with anathor type,but i don·t want to change too ..  
    foreach ($aqConfig as $k => $v) {
        if (in_array($k, $viewField)) {
            $kArr[] = $k;
            if ($v == 4) {
                $dataArr[] = $k;
            }
            if ($v == 3) {
                $textAreaArr[] = $k;
            }
        }
    }

    if ($searchField) {
        outPutSearch($searchField);
    }
    //if is search , post change to get
    if ($P['aq_submit']) {
        aqMessage('正在为您展示搜索结果...', 'aq提示', 'index.php?action=' . $P[action] . '&searchType=' . $P[searchType] . '&searchKey=' . $P[searchKey], 2);
        exit;
    }
    $page = $G['page'] ? $G[page] : 1;
    $start = ($page - 1) * $perpage;
    if ($G['searchType'] && $G['searchKey']) {
        $search_ = $G['searchType'] . '_S';
        $search_S = function_exists($search_);
        $counts = $db->fetch_first("SELECT count(*) as c FROM " . $tablename . " WHERE " . $G[searchType] . " " . ($search_S ? 'like' : '=') . " '" . ($search_S ? '%' : '') . "" . $G[searchKey] . "" . ($search_S ? '%' : '') . "'");
    } else {
        $counts = $db->fetch_first("SELECT count(*) as c FROM " . $tablename);
    }
    $count = $counts['c'];
    //if is search  'sql' use 'url' 
    if ($G['searchType'] && $G['searchKey'])
        $sql = $db->query("SELECT " . implode(',', $kArr) . " FROM " . $tablename . " WHERE " . $G[searchType] . " " . ($search_S ? 'like' : '=') . " '" . ($search_S ? '%' : '') . "" . $G[searchKey] . "" . ($search_S ? '%' : '') . "' LIMIT " . $start . " , " . $perpage);
    else // if is not search 
        $sql = $db->query("SELECT " . implode(',', $kArr) . " FROM " . $tablename . " " . $order . " LIMIT " . $start . " , " . $perpage);
    //if is search page use 'url'
    if ($G['searchType'] && $G['searchKey'])
        $aq_page = multi($count, $perpage, $page, "index.php?action=$G[action]&do=index&searchType=" . $G['searchType'] . "&searchKey=" . $G['searchKey']);
    else  //if is not search
        $aq_page = multi($count, $perpage, $page, "index.php?action=$G[action]");

    //each the sql
    while ($res = $db->fetch_array($sql)) {
        // if is date  , change type of date
        if ($dataArr) {
            foreach ($dataArr as $v) {
                $res[$v] = date('Y-m-d H:i:s', $res[$v]);
            }
        }
        //if is textarea, cut the result ' and change type of result
        if ($textAreaArr) {
            foreach ($textAreaArr as $v) {
                $res[$v] = htmlspecialchars($res[$v]); //防止列表中的内容出现html代码，造成页面错位
                $res[$v] = cutstr($res[$v], 40);
            }
        }
        //one result into array
        $aqList[] = $res;
    }

    //if the result is empty , how to do
    if (empty($aqList) && !$G['searchType'] && !$G['searchKey']) { // if is not search
        if ($page > 1) {
            aqMessage1('第' . $page . '页没有数据,正在自动跳到上一页', 'aq提示', 'index.php?action=' . $G['action'] . '&page=' . ($page - 1), 2);
        } else {
            aqMessage1('第' . $page . '页都没有数据，快来添加吧', 'aq提示', 'index.php?action=' . $G['action'] . '&do=add', 2);
        }
    } elseif (empty($aqList) && $G['searchType'] && $G['searchKey']) {  // if is search
        if ($page > 1) {
            aqMessage1('第' . $page . '页没有数据,正在自动跳到上一页', 'aq提示', 'index.php?action=' . $G[action] . '&searchType=' . $G[searchType] . '&searchKey=' . $G[searchKey] . '&page=' . ($page - 1), 2);
        } else {
            aqMessage1('第' . $page . '页没有数据', 'aq提示', '', 2);
        }
    }

    // output the title of field
    echo '<div class="bloc"><div class="title">';
    echo $tableTitle . "列表";
    echo '<a class="toggle" href="#"></a><a href="#" class="toggle"></a></div><div class="content"><table><thead><tr><th><input type="checkbox" class="checkall"></th> ';
    foreach ($fieldTitle as $k => $v) {
        if (in_array($k, $viewField))
            echo "<th style='text-align:left;'>$v</th>";
    }
    //if mainid is not empty , then have the title of edit,delete
    if ($mainId)
        echo '<th style="text-align:right;">操作</th>';
    formHead($G['action'], 'deleteAll');  //out put the formhead
    //if do deleteAll, can use these
    echo '<input type="hidden" name="page" value="' . $page . '">';
    echo '<input type="hidden" name="searchType" value="' . $G[searchType] . '">';
    echo '<input type="hidden" name="searchKey" value="' . $G[searchKey] . '">';

    //out put the field , if field is in viewField array
    foreach ($aqList as $v) {

        echo '</tr></thead><tbody><tr><td><input type="checkbox" name="idArr[]" value="' . $v[$mainId] . '"></td>';
        foreach ($kArr as $vk) {
            $func = $vk . 'Anathor';
            if (function_exists($func)) {
                $v[$vk] = $func($v[$vk]);
            }
            echo "<td>$v[$vk]</td>";
        }
        //if have mainid , then have edit and delete
        if ($mainId)
            echo '<td class="actions"><a title="编辑此列" href="index.php?action=' . $G['action'] . '&do=edit&id=' . $v[$mainId] . '&page=' . $page . '&searchKey=' . $G[searchKey] . '&searchType=' . $G[searchType] . '"><img alt="" src="images/edit.png"></a><a target="aq_iframe" onclick="return(confirm(\'确定删除?\'))" title="删除此列" href="index.php?action=' . $G['action'] . '&do=delete&id=' . $v[$mainId] . '&page=' . $page . '&searchType=' . $G[searchType] . '&searchKey=' . $G[searchKey] . '"><img alt="" src="images/delete.png"></a></td></tr>';
    }
    //have action deleteAll and add
    echo '</tbody></table><div class="left div" style="padding-top:8px;"><div class="submit"><input type="submit" value="删除" onclick="return(confirm(\'确定删除?\'))" name="aq_delete"><a href="index.php?action=' . $G['action'] . '&do=add"><input type="button" onclick="window.location.href=\'index.php?action=' . $G['action'] . '&do=add\'" value="添加" name="aq_add"></a><a href="index.php?action=' . $G['action'] . '&do=getExcel" target="aq_iframe"><input type="button" value="生成excel" name="aq_add"></a></div></div>' . $aq_page . '</div></div></form>';

    if (in_array(8, $aqConfig)) {
        echo <<<EOF
   <script>
       
     function viewPic(pic){
             art.dialog({
            padding: 0,
            title: '照片',
            content: '<img src='+pic+' />',
            lock: true  
        });
    }
</script>
EOF;
    }
}

//if searchField is not empty, out put the search
function outPutSearch($searchField) {
    global $aqConfig, $tableTitle, $G, $fieldTitle;
    echo '<div class="bloc"><div class="title">' . $tableTitle . '搜索<a href="#" class="toggle"></a></div><div class="content">';
    formHead($G['action'], 'index');
    echo "<input type='hidden' name='action' value='$G[action]'>";
    echo "<input type='hidden' name='do' value='index'>";
    echo '<div class="input"><label for="select">选择搜索类型:</label><div class="selector" id="uniform-select"><span>First value</span><select name="searchType" id="select" style="opacity: 0; ">';
    foreach ($searchField as $v) {
        echo "<option value='$v' " . ($G['searchType'] == $v ? 'seleced=true' : '') . ">$fieldTitle[$v]</option>";
    }
    echo '</select></div></div>';
    echo '<div class="input"><label for="input1">输入关键字：</label><input type="text" id="input1" value="' . $G['searchKey'] . '" name="searchKey"></div>';
    echo '<div class="submit"><input type="submit" value="提交" name="aq_submit"></div>';
    echo '</form></div></div>';
}

function edit() {
    global $tablename, $aqConfig, $fieldTitle, $editField, $tableTitle, $mainId, $perpage, $db, $G, $P;
    if ($P['aq_submit']) {
        if (function_exists('editBefore')) {
            editBefore();
        }
        $editArr = array();
        foreach ($editField as $v) {
            $checkFunc = $v . 'Check';
            if (function_exists($checkFunc)) {
                if ($aqConfig[$v] == 6 && empty($P[$v])) {
                    
                } else {
                    if ($checkFunc($P[$v]) != '1') {
                        aqError('Error', $checkFunc($P[$v]));
                        exit;
                    }
                }
            }

            if ($aqConfig[$v] == 4) {
                $P[$v] = strtotime($P[$v]);
            }

            if ($aqConfig[$v] == 9) {
                $Func = $v . 'Code';
                if (function_exists($Func)) {
                    $P[$v] = $Func($P[$v]);
                }
            }

            if ($aqConfig[$v] == 8) {
                $Func = $v . 'Code';
                if (function_exists($Func)) {
                    $P[$v] = $Func($_FILES, $v);
                }
            }

            if ($aqConfig[$v] == 7) {
                $P[$v] = addslashes($P[$v]);
            }

            if ($aqConfig[$v] == 3) {
                $P[$v] = addslashes($P[$v]);
            }

            if ($aqConfig[$v] == 2) {
                $P[$v] = addslashes($P[$v]);
            }

            if ($aqConfig[$v] == 6) {
                if (empty($P[$v])) {
                    continue;
                } else {
                    $pasFunc = $v . 'Code';
                    $P[$v] = $pasFunc($P[$v]);
                }
            }
            if ($P[$v])
                $editArr[] = $v . "='" . $P[$v] . "'";
        }
        $db->query("UPDATE " . $tablename . " SET " . implode(',', $editArr) . " WHERE " . $mainId . " = " . $P[$mainId]);
        if (function_exists('editAfter')) {
            editAfter();
        }
        if ($P['searchType'] && $P['searchKey']) {
            aqMessage('编辑成功', 'aq提示', "index.php?action=" . $G['action'] . "&page=" . $P['page'] . "&searchType=" . $P['searchType'] . "&searchKey=" . $P['searchKey'], 2);
        } else {
            aqMessage('编辑成功', 'aq提示', "index.php?action=" . $G['action'] . "&page=" . $P['page'], 2);
        }
    } else {
        $editArr = array();
        foreach ($editField as $v) {
            $editArr[] = $v;
        }
        $oneArr = $db->fetch_first("SELECT " . implode(',', $editArr) . " FROM " . $tablename . " WHERE " . $mainId . " = " . $G['id']);
        formHead($G['action'], 'edit');
        echo '<div class="bloc"><div class="title">' . $tableTitle . '编辑<a href="#" class="toggle"></a></div><div class="content">';
        echo "<input type='hidden' value='$G[id]' name='$mainId'>";
        echo "<input type='hidden' value='$G[page]' name='page'>";
        echo "<input type='hidden' value='$G[searchType]' name='searchType'>";
        echo "<input type='hidden' value='$G[searchKey]' name='searchKey'>";
        echo '<div  id="error"></div>';
        foreach ($editField as $v) {
            if ($aqConfig[$v] == 1 || $aqConfig[$v] == 2) {
                echo '<div class="input"><label for="input1">' . $fieldTitle[$v] . '：</label><input type="text" id="input1" value="' . $oneArr[$v] . '" name="' . $v . '"></div>';
            }
            if ($aqConfig[$v] == 3) {
                $oneArr[$v] = stripslashes($oneArr[$v]);
                echo '<script type="text/javascript" src="ueditor/editor_config.js"></script>';
                echo '<script type="text/javascript" src="ueditor/editor_all.js"></script>';
                echo '<link rel="stylesheet" href="ueditor/themes/default/ueditor.css"/>';
                /* echo '<div class="input textarea"><label for="textarea1">'.$fieldTitle[$v].'</label><textarea name="'.$v.'"  id="textarea1" rows="7" cols="4">'.$oneArr[$v].'</textarea></div>'; */
                echo '<div class="input textarea"><label for="textarea1">' . $fieldTitle[$v] . ':</label></div>';
                echo ' <script type="text/plain" id="myEditor">' . $oneArr[$v] . '</script>
				<script type="text/javascript">
					var editor = new baidu.editor.ui.Editor(
					{
						textarea:\'' . $v . '\',
						relativePath:false,
						imagePath:\'\',
						autoHeightEnabled:false,
						UEDITOR_HOME_URL:\'' . $aq_site . 'ueditor/\'
					}
				);
   				 editor.render("myEditor");
				</script>';
            }

            if ($aqConfig[$v] == 9) {
                echo '<div class="input"><label class="label">' . $fieldTitle[$v] . ':</label>';
                $checkboxdFun = $v . 'Checkbox';
                $checkboxdFun($oneArr[$v]);
                echo '</div>';
            }

            if ($aqConfig[$v] == 8) {
                echo '<div class="input"><label for="file">' . $fieldTitle[$v] . '</label><input type="file" name="' . $v . '" id="file"></div>';
            }

            if ($aqConfig[$v] == 7) {
                echo '<div class="input textarea"><label for="textarea1">' . $fieldTitle[$v] . '</label><textarea name="' . $v . '"  id="textarea1" rows="7" cols="4">' . $oneArr[$v] . '</textarea></div>';
            }

            if ($aqConfig[$v] == 4) {
                $oneArr[$v] = date('m/d/Y', $oneArr[$v]);
                echo '<div class="input"><label for="input4">' . $fieldTitle[$v] . '</label><input type="text" value="' . $oneArr[$v] . '" class="datepicker" id="input4" name="' . $v . '"></div>';
            }
            if ($aqConfig[$v] == 5) {
                echo '<div class="input"><label for="select">' . $fieldTitle[$v] . '</label><div class="selector" id="uniform-select"><span>First value</span><select name="' . $v . '" id="select" style="opacity: 0; ">';
                $optionFun = $v . 'Option';
                $optionFun($oneArr[$v]);
                echo '</select></div></div>';
            }
            if ($aqConfig[$v] == 6) {
                echo '<div class="input"><label for="input1">' . $fieldTitle[$v] . '：</label><input type="text" id="input1" value="" name="' . $v . '"><font color="red">如不重设密码，则为空即可</font></div>';
            }
        }
        formFooter();
        echo "</div></div>";
    }
}

function add() {
    global $tablename, $aqConfig, $fieldTitle, $editField, $tableTitle, $mainId, $perpage, $db, $G, $P;
    if ($P['aq_submit']) {
        if (function_exists('addBefore')) {
            addBefore();
        }
        $valuesArr = '';
        foreach ($editField as $v) {
            $checkFunc = $v . 'Check';
            if (function_exists($checkFunc)) {
                if ($checkFunc($P[$v]) != '1') {
                    aqError('Error', $checkFunc($P[$v]));
                    exit;
                }
            }
            $editArr[] = $v;
            if ($aqConfig[$v] == 4) {
                $P[$v] = strtotime($P[$v]);
            }
            if ($aqConfig[$v] == 6) {
                $pasFunc = $v . 'Code';
                $P[$v] = $pasFunc($P[$v]);
            }

            if ($aqConfig[$v] == 8) {
                $Func = $v . 'Code';
                if (function_exists($Func)) {
                    $P[$v] = $Func($_FILES, $v);
                }
            }

            if ($aqConfig[$v] == 7) {
                $P[$v] = addslashes($P[$v]);
            }

            if ($aqConfig[$v] == 2) {
                $P[$v] = addslashes($P[$v]);
            }

            if ($aqConfig[$v] == 3) {
                $P[$v] = addslashes($P[$v]);
            }
            $valuesArr[] = "'" . $P[$v] . "'";
        }
        $db->query("Insert INTO " . $tablename . "  ( " . implode(',', $editArr) . " ) VALUES ( " . implode(',', $valuesArr) . " )");
        if (function_exists('addAfter')) {
            addAfter();
        }
        aqMessage('添加成功', 'aq提示', "index.php?action=" . $G['action'], 2);
    } else {
        formHead($G['action'], 'add');
        echo '<div class="bloc"><div class="title">添加' . $tableTitle . '<a href="#" class="toggle"></a></div><div class="content">';
        echo '<div  id="error"></div>';
        foreach ($editField as $v) {
            if ($aqConfig[$v] == 1 || $aqConfig[$v] == 2 || $aqConfig[$v] == 6) {
                echo '<div class="input"><label for="input1">' . $fieldTitle[$v] . '：</label><input type="text" id="input1" name="' . $v . '"></div>';
            }
            if ($aqConfig[$v] == 3) {
                echo '<script type="text/javascript" src="ueditor/editor_config.js"></script>';
                echo '<script type="text/javascript" src="ueditor/editor_all.js"></script>';
                echo '<link rel="stylesheet" href="ueditor/themes/default/ueditor.css"/>';

                /* echo '<div class="input textarea"><label for="textarea1">'.$fieldTitle[$v].'</label><textarea name="'.$v.'" id="textarea1" rows="7" cols="4"></textarea></div>'; */
                echo '<div class="input textarea"><label for="textarea1">' . $fieldTitle[$v] . ':</label></div>';
                echo '<div id="myEditor"></div>
<script type="text/javascript">
var editor = new baidu.editor.ui.Editor(
			{
				textarea:\'' . $v . '\',
				relativePath:false,
				imagePath:\'\',
				autoHeightEnabled:false,
				UEDITOR_HOME_URL:\'' . $aq_site . 'ueditor/\'
			}
);
    editor.render("myEditor");
</script>';
            }

            if ($aqConfig[$v] == 9) {
                echo '<div class="input"><label class="label">' . $fieldTitle[$v] . '</label>';
                $checkboxdFun = $v . 'Checkbox';
                $checkboxdFun();
                echo '</div>';
            }

            if ($aqConfig[$v] == 8) {
                echo '<div class="input"><label for="file">' . $fieldTitle[$v] . '</label><input type="file" name="' . $v . '" id="file"></div>';
            }

            if ($aqConfig[$v] == 7) {
                echo '<div class="input textarea"><label for="textarea1">' . $fieldTitle[$v] . '</label><textarea name="' . $v . '" id="textarea1" rows="7" cols="4"></textarea></div>';
            }
            if ($aqConfig[$v] == 4) {
                echo '<div class="input"><label for="input4">' . $fieldTitle[$v] . '</label><input type="text" class="datepicker" id="input4" name="' . $v . '"></div>';
            }
            if ($aqConfig[$v] == 5) {
                echo '<div class="input"><label for="select">' . $fieldTitle[$v] . '</label><div class="selector" id="uniform-select"><span>First value</span><select name="' . $v . '" id="select" style="opacity: 0; ">';
                $optionFun = $v . 'Option';
                $optionFun();
                echo '</select></div></div>';
            }
        }
        formFooter();
        echo "</div></div>";
    }
}

function delete() {
    global $db, $G, $mainId, $tablename;
    $db->query("DELETE FROM " . $tablename . " WHERE " . $mainId . " = " . $G['id']);
    if ($G['searchType'] && $G['searchKey']) {
        aqMessage('操作完成', 'aq提示', 'index.php?action=' . $G['action'] . '&page=' . $G['page'] . '&searchType=' . $G[searchType] . '&searchKey=' . $G[searchKey], 2);
    } else {
        aqMessage('操作完成', 'aq提示', 'index.php?action=' . $G['action'] . '&page=' . $G['page'], 2);
    }
}

function deleteAll() {
    global $db, $P, $G, $mainId, $tablename;
    $db->query("DELETE FROM " . $tablename . " WHERE " . $mainId . " IN ( " . implode(',', $P['idArr']) . " )");
    if ($P['searchType'] && $P['searchKey']) {
        aqMessage('删除成功', 'aq提示', "index.php?action=" . $G['action'] . "&page=" . $P['page'] . "&searchType=" . $P['searchType'] . "&searchKey=" . $P['searchKey'], 2);
    } else {
        aqMessage('删除成功', 'aq提示', "index.php?action=" . $G['action'] . "&page=" . $P['page'], 2);
    }
}

function getExcel() {
    global $fieldTitle,$db,$tablename,$order;
    // ini_set('include_path', ini_get('include_path') . ';../Classes/');
    include AQ_ROOT . '/PHPExcel.php';
    include AQ_ROOT . '/PHPExcel/Writer/Excel2007.php';
    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()->setCreator("zhang liu");
    $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
    $objPHPExcel->getProperties()->setTitle($title);
    $objPHPExcel->getProperties()->setSubject("发帖频率统计");
    $objPHPExcel->getProperties()->setDescription("powered by ifeng.com");
    $objPHPExcel->setActiveSheetIndex(0);

    $zm = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $t_i = 1;
    foreach ($fieldTitle as $k => $v) {
        $search[] = $k;
        $objPHPExcel->getActiveSheet()->SetCellValue($zm[$t_i - 1] . '1', $v);
        $t_i++;
    }
    $res = $db->query("SELECT ".(implode(',',$search))." FROM $tablename $order ");
    $t = 2;
    while($value = $db->fetch_array($res)){
        foreach($search as $k=>$v){
            $objPHPExcel->getActiveSheet()->SetCellValue($zm[$k].$t , $value[$v]);
        }
        $t++;
    }

    $title = "excel";
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $file_path = AQ_ROOT . '/' . $title . '.xlsx';
    $fileName = $title . '.xlsx';
    if (file_exists($file_path))
        unlink($file_path);
    $objWriter->save($file_path);

    ob_start();
    $file = fopen($file_path, "r");
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length: " . filesize($file_path));
    Header("Content-Disposition: attachment; filename=" . $fileName);
    echo fread($file, filesize($file_path));
    fclose($file);
}
?>

