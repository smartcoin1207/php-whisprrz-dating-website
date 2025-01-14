<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class cTable{
  var $caption;     //???????? ???????
  var $table;         //??? ??????? ? ??
  var $is_rank;     //1 - ? ??????? ???????? ??????? ???? ????
  var $is_status;     //1 - ? ??????? ???????? ??????? ???? status
  var $is_add;      // 0 - ?????? ??????????, 00 - ????????????, 1 ? ????? - ????? ??????????? ????????? ?????
  var $is_edit;      // 0 - ?????? ?????????,
  var $is_del;      // 0 - ?????? ????????,
  var $id_key;  // ??? ??????????? id ??????
  var $where = '';  // ??????? ???? ?????
  var $actions = Array();
  var $cols = Array();
  var $page_size;  //?????????? ????? ?? ????????
	/* ??????????? */
	function __construct($caption, $table="", $id_key='id' , $is_rank=0, $is_status=0, $is_add=1,$is_edit=1,$is_del=1){
		$this->caption = $caption;
		$this->table = $table;
		$this->id_key=$id_key;
		$this->is_rank = $is_rank;
		$this->is_status = $is_status;
		$this->is_add = $is_add;
		$this->is_edit = $is_edit;
		$this->is_del = $is_del;
		$this->page_size = 100;
	}
	function settings($where){
		$this->where = $where;
	}
	function set_page_size($page_size){
		$this->page_size = $page_size;
	}

	function insertcol($caption, $name, $list, $edit, $coltype, $param1 = '',$param2 = '',$param3 = ''){
		$indx = sizeof($this->cols)+1;
		$this->cols[$indx]['caption'] = $caption;
		$this->cols[$indx]['name'] = $name;
		$this->cols[$indx]['list'] = $list;
		$this->cols[$indx]['edit'] = $edit;
		$this->cols[$indx]['coltype'] = $coltype;
		$this->cols[$indx]['param1'] = $param1;
		$this->cols[$indx]['param2'] = $param2;
		$this->cols[$indx]['param3'] = $param3;
		// caption - ????????? ??????? (???.)
		// name - ??? ??????? (????.)
		// list - ???? true, ???? ???????????? ? ????? ??????
		// edit - ???? true, ???? ?????????????
		// coltype - ??? ???????
		//  $param1 = '',$param2 = '',$param3  - ????????? ???????
		// colparams - ????????? ???????
		//$this->cols[$indx]['colparams'] = $colparams;
	}
	function insert_action($caption, $name, $id_name){
		$indx = sizeof($this->actions)+1;
		$this->actions[$indx]['caption'] = $caption;
		$this->actions[$indx]['name'] = $name;
		$this->actions[$indx]['id_name'] = $id_name;
		// caption - ????????? action (???.)
		// name - ??? ?????(????.)
		//	id - ??? id ? ????? ?????
	}
	function insertparam($param, $value){
		$this->insertcol('', $param, 0, 0, 'parametr' , $value ,$param2 = '',$param3 = '');
	}

	function reorder(){
		$q=new query();
		$sql = 'select '.$this->id_key.' from '.$this->table;
		if($this->where) $sql .= ' where '.$this->where;
		$sql .= ' order by rank';
		$OrdList = $q->select($sql);
		$n=1;
			foreach ($OrdList as $row){
			$sql = 'update '.$this->table.' set rank = '.$n.' where '.$this->id_key.' =  '.$row[$this->id_key];
			$q->exec($sql);
			$n++;
		}
	}

	function draw(){
		global $inc_path,$PHP_SELF;
		$q=new query();



		if(get_param('reorder') == "true" ){
			$this->reorder();
		}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////// ADD ///////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "add" ){
			echo '<!--  PopCalendar(tag name and id must match) Tags should not be enclosed in tags other than the html body tag. -->
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="'.$inc_path.'class/calendar/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>';

			echo '<form name="table_form" method="POST" enctype="multipart/form-data"><input type="hidden" name="tmode" value="add_row">
			<input type="submit" value="Add"   class="btn" ><table border="1" class="edit_table">';
			foreach($this->cols as $k=>$v){
				if($this->cols[$k]['edit']){
					switch($this->cols[$k]['coltype']){

						case 'text' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="text" name="new_'.$this->cols[$k]['name'].'" size="'.$this->cols[$k]['param1'].'" maxlength="'.$this->cols[$k]['param2'].'"></td></tr>';
							break;
						case 'data' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="text" name="new_'.$this->cols[$k]['name'].'" value="'.$data[$this->cols[$k]['name']].'">
									<a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.table_form.new_'.$this->cols[$k]['name'].');return false;" ><img class="PopcalTrigger" align="absmiddle" src="'.$inc_path.'class/calendar/calbtn.gif" width="34" height="22" border="0" alt=""></a>';


									echo '</td></tr>';

										break;
						case 'textarea' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><textarea name="new_'.$this->cols[$k]['name'].'"  cols="'.$this->cols[$k]['param1'].'" rows="'.$this->cols[$k]['param2'].'"></textarea></td></tr>';
							break;

						case 'image' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="file" name="new_'.$this->cols[$k]['name'].'" ></td></tr>';
							break;
						case 'file_id' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="file" name="new_'.$this->cols[$k]['name'].'" ></td></tr>';
							break;
						case 'anyfile' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="file" name="new_'.$this->cols[$k]['name'].'" ></td></tr>';
							break;
						case 'select' :
							echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><select name="new_'.$this->cols[$k]['name'].'" >';
							foreach($this->cols[$k]['param1'] as $k1=>$v1){
								echo '<option value="'.$k1.'">'.$v1;
							}
							echo '</select></td></tr>';
							break;

						case 'fck' :
									echo '<tr><td valign="top">'.$this->cols[$k]['caption'].'</td><td width="100%">';
									$sBasePath = $inc_path.'class/fck/';
									$oFCKeditor[$k] = new FCKeditor('new_'.$this->cols[$k]['name']) ;
									$oFCKeditor[$k]->BasePath	= $sBasePath ;
									$oFCKeditor[$k]->Width = '100%' ;
									if(empty($this->cols[$k]['param1'])) $this->cols[$k]['param1'] = '400';
									$oFCKeditor[$k]->Height = $this->cols[$k]['param1'] ;
									$oFCKeditor[$k]->Value		= '' ;
									$oFCKeditor[$k]->Create() ;
									echo '</td></tr>';
									break;


					}
				}
			}
			echo '</table>';

			echo '<input type="submit" value="Add"   class="btn" ></form>';

				echo '<form method="POST">
				<input type="hidden" name="tmode" value="">
				<input type="submit" value="Cancel"  class="btn" ></form>';




		}
/////////////////////////////////////////////////////////end ADD//////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////ADD INSERT///////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "add_row" ){
			$sql = "insert into ".$this->table." set ";

			foreach($this->cols as $k=>$v){
				if($this->cols[$k]['coltype'] == 'parametr'){
					$sql .= " ".$this->cols[$k]['name']." = '".$this->cols[$k]['param1']."',";
				}

				if($this->cols[$k]['edit']){
					switch($this->cols[$k]['coltype']){
						case 'text' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('new_'.$this->cols[$k]['name']))."',";
									break;
						case 'data' :
								$temp = get_param('new_'.$this->cols[$k]['name']);
								$str_data = substr($temp,6,4).substr($temp,3,2).substr($temp,0,2);
								$sql .= " ".$this->cols[$k]['name']." = '".$str_data."',";
								break;
						case 'textarea' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('new_'.$this->cols[$k]['name']) )."',";
									break;

						case 'fck' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('new_'.$this->cols[$k]['name']))."',";
									break;

						case 'image' :
								if(is_file($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'])){
									$ext = array(".gif", ".jpg", ".jpeg", ".png");
									for($i=0;$i<sizeof($ext);$i++){
										if($data = explode($ext[$i], $_FILES['new_'.$this->cols[$k]['name']]['name'])){
											if(count($data) == 2){
												$pict_ext = $ext[$i];
												break;
											}
										}
									}
								}
								$sql .= " ".$this->cols[$k]['name']." = '".$pict_ext."',";
								break;
							case 'file_id' :
								if(is_file($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'])){
									$str_temp = $_FILES['new_'.$this->cols[$k]['name']]['name'];
									for($i=strlen($str_temp);$i>0;$i--){
										if($str_temp[$i] == '.') break;
									}
									$file_ext = substr($str_temp,$i);
									$sql .= " ".$this->cols[$k]['name']." = '".$file_ext."',";
								}
								break;
							case 'anyfile' :
								if(is_file($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'])){
									$file_name = $_FILES['new_'.$this->cols[$k]['name']]['name'];
									$sql .= " ".$this->cols[$k]['name']." = '".$file_name."',";
									$path = $this->cols[$k]['param1'];
									$filename = $path.$file_name;
									copy($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'], $filename);
								}
								break;
							case 'select' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('new_'.$this->cols[$k]['name']))."',";
									break;
					}
				}
			}
			if($this->is_rank){
				$q_max = "select max(rank)+1 as max from ".$this->table;
				if($this->where) $q_max .= " where ".$this->where;
				$max = $q->select1($q_max);
				$sql .= " rank = '".$max['max']."',";
			}

			$sql=substr($sql, 0, strlen($sql)-1);
			$new_id = $q->insert($sql);

			foreach($this->cols as $k=>$v){
				if($this->cols[$k]['edit']){
					switch($this->cols[$k]['coltype']){
							case 'image' :
								if(is_file($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'])){
									$ext = array(".gif", ".jpg", ".jpeg", ".png");
									for($i=0;$i<sizeof($ext);$i++){
										if($data = explode($ext[$i], $_FILES['new_'.$this->cols[$k]['name']]['name'])){
											if(count($data) == 2){
												$pict_ext = $ext[$i];
												break;
											}
										}
									}
								}
								if(!empty($pict_ext )){
									$path = $this->cols[$k]['param1'];
									$filename = $path.$new_id.$pict_ext;
									copy($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'], $filename);
									list($w, $h) = custom_getimagesize($filename);
									$h = round(($h/($w/50)), 0);
									$w=50;
									resize_then_crop($filename,$path.'pre'.$new_id.$pict_ext,$w,$h,255,255,255);
								}
								break;
							case 'file_id' :
								$file_ext = '';
								if(is_file($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'])){
									$str_temp = $_FILES['new_'.$this->cols[$k]['name']]['name'];
									for($i=strlen($str_temp);$i>0;$i--){
										if($str_temp[$i] == '.') break;
									}
									$file_ext = substr($str_temp,$i);
								}
								if(!empty($file_ext )){
									$path = $this->cols[$k]['param1'];
									$filename = $path.$new_id.$file_ext;
									copy($_FILES['new_'.$this->cols[$k]['name']]['tmp_name'], $filename);
								}
								break;

					}
				}
			}

			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end ADD INSERT///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////EDIT/////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "edit" ){
			$id_to_edit = array();//?????? id ??????? ????? ???????????????
			foreach($_POST as $k=>$v){
				if (substr($k,0,9) == 'ch_action'){
					$id_to_edit[count($id_to_edit)] = substr($k, 9);
				}

			}
			$all_ids = implode("|", $id_to_edit);
			echo '<form method="POST" >
			<input type="hidden" name="tmode" value="">
			<input type="submit" value="Cancel"  class="btn" ></form>';

			echo '<form method="POST" name="table_form" enctype="multipart/form-data">
			<input type="submit" value="Edit"  class="btn" >
			<table border="0"  width="100%">
			<input type="hidden" name="tmode" value="edit_rows">
			<input type="hidden" name="rows_to_edit" value="'.$all_ids.'">';


			echo '<!--  PopCalendar(tag name and id must match) Tags should not be enclosed in tags other than the html body tag. -->
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="'.$inc_path.'class/calendar/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>';

			foreach($id_to_edit as $id_row){
				echo '<tr><td  width="100%">';

				$sql = "select ";
				foreach($this->cols as $k=>$v){
					if($this->cols[$k]['edit']){
						if($this->cols[$k]['coltype'] == 'data'){
							$sql.= 'DATE_FORMAT( '.$this->cols[$k]['name'].' , \'%d.%m.%Y\' ) as '.$this->cols[$k]['name'];
						}else{
							$sql.=$this->cols[$k]['name'];
						}
						//$sql.=$this->cols[$k]['name'];
						$sql.=', ';
					}
				}
				$sql.= $this->id_key;
				$sql.= " from ".$this->table." where ". $this->id_key."='".$id_row."' ";
				$data = $q->select1($sql);

				echo '<table border="1"  width="100%" class="edit_table">';
				foreach($this->cols as $k=>$v){
					if($this->cols[$k]['edit']){
						switch($this->cols[$k]['coltype']){
							case 'text' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="text" name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" value="'.htmlspecialchars($data[$this->cols[$k]['name']]).'" size="'.$this->cols[$k]['param1'].'" maxlength="'.$this->cols[$k]['param2'].'"></td></tr>';
										break;
							case 'data' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><input type="text" name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" value="'.$data[$this->cols[$k]['name']].'">
									<a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.table_form.edit_'.$id_row.'_'.$this->cols[$k]['name'].');return false;" ><img class="PopcalTrigger" align="absmiddle" src="'.$inc_path.'class/calendar/calbtn.gif" width="34" height="22" border="0" alt=""></a>';


									echo '</td></tr>';

										break;
							case 'textarea' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><textarea name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'"   cols="'.$this->cols[$k]['param1'].'" rows="'.$this->cols[$k]['param2'].'">'.$data[$this->cols[$k]['name']].'</textarea></td></tr>';
										break;
							case 'select' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" ><select name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" >';
									foreach($this->cols[$k]['param1'] as $k1=>$v1){
										echo '<option value="'.$k1.'" ';
										if ($data[$this->cols[$k]['name']] == $k1) echo ' selected ';
										echo '>'.$v1;
									}
									echo '</select></td></tr>';
									break;

							case 'fck' :
									echo '<tr><td valign="top">'.$this->cols[$k]['caption'].'</td><td width="100%">';
									$sBasePath = $inc_path.'class/fck/';
									$oFCKeditor[$k][$id_row] = new FCKeditor('edit_'.$id_row.'_'.$this->cols[$k]['name']) ;
									$oFCKeditor[$k][$id_row]->BasePath	= $sBasePath ;
									$oFCKeditor[$k][$id_row]->Width = '100%' ;
									if(empty($this->cols[$k]['param1'])) $this->cols[$k]['param1'] = '400';
									$oFCKeditor[$k][$id_row]->Height = $this->cols[$k]['param1'] ;
									//$oFCKeditor[$k][$id_row]->Height = '400' ;
									$oFCKeditor[$k][$id_row]->Value = $data[$this->cols[$k]['name']] ;
									$oFCKeditor[$k][$id_row]->Create() ;
									echo '</td></tr>';
									break;

							case 'image' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" >';
										$path = $this->cols[$k]['param1'];
										$filename = $path.'pre'.$id_row.$data[$this->cols[$k]['name']];
										if(file_exists($filename)){
											echo '<img src="'.$filename.'" align="left">';
										}


									echo '<input type="file" name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" onchange="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'[1].checked=true;"><br>';
									echo '<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" checked value="no">no change
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="yes"> Edit
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="del">Delete';
									echo '</td></tr>';
									break;
							case 'file_id' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" >';
										$path = $this->cols[$k]['param1'];
										$file_name = $id_row.$data[$this->cols[$k]['name']];
										$file_path = $path.$file_name;
										if(file_exists($file_path)){
											echo $data[$this->cols[$k]['name']].' ';
										}


									echo '<input type="file" name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" onchange="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'[1].checked=true;" ><br>';
									echo '<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" checked value="no">no change
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="yes"> Edit
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="del">Delete';
									echo '</td></tr>';
									break;

							case 'anyfile' :
									echo '<tr><td valign="top" >'.$this->cols[$k]['caption'].'</td><td valign="top" >';
										$path = $this->cols[$k]['param1'];
										$file_name = $data[$this->cols[$k]['name']];
										$file_path = $path.$file_name;
										if(file_exists($file_path)){
											echo $data[$this->cols[$k]['name']].' ';
										}

									echo '<input type="file" name="edit_'.$id_row.'_'.$this->cols[$k]['name'].'" onchange="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'[1].checked=true;" ><br>';
									echo '<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" checked value="no">no change
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="yes"> Edit
										<input type="radio" name="editrad_'.$id_row.'_'.$this->cols[$k]['name'].'" value="del">Delete';
									echo '</td></tr>';
									break;


						}
					}
				}
				echo '</table>';

				echo '</td></tr>';
				echo '<tr><td><hr></td></tr>';

			}
			echo '</table>';
			echo '<input type="submit" value="Edit"  class="btn" ></form>';

			echo '<form method="POST">
			<input type="hidden" name="tmode" value="">
			<input type="submit" value="Cancel"  class="btn" ></form>';




		}
/////////////////////////////////////////////////////////end EDIT///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////UPDATE//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "edit_rows" ){
			$id_to_edit = explode("|", get_param('rows_to_edit'));
			foreach($id_to_edit as $id_row){
				if(!$id_row) continue;
				$sql = "update  ".$this->table." set ";

				foreach($this->cols as $k=>$v){
					if($this->cols[$k]['edit']){
						switch($this->cols[$k]['coltype']){
							case 'text' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('edit_'.$id_row.'_'.$this->cols[$k]['name']))."',";
											break;
							case 'data' :
								$temp = get_param('edit_'.$id_row.'_'.$this->cols[$k]['name']);
								$str_data = substr($temp,6,4).substr($temp,3,2).substr($temp,0,2);
								$sql .= " ".$this->cols[$k]['name']." = '".$str_data."',";


											break;
							case 'select' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('edit_'.$id_row.'_'.$this->cols[$k]['name']))."',";
											break;
							case 'textarea' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('edit_'.$id_row.'_'.$this->cols[$k]['name']))."',";
											break;
							case 'fck' : $sql .= " ".$this->cols[$k]['name']." = '".addslashes(get_param('edit_'.$id_row.'_'.$this->cols[$k]['name']))."',";
											break;
							case 'image' :
												if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
													if(is_file($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'])){
														$ext = array(".gif", ".jpg", ".jpeg", ".png");
														for($i=0;$i<sizeof($ext);$i++){
															if($data = explode($ext[$i], $_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['name'])){
																if(count($data) == 2){
																	$pict_ext = $ext[$i];
																	break;
																}
															}
														}
													}
													if(!empty($pict_ext )){
														$path = $this->cols[$k]['param1'];
														$filename = $path.$id_row.$pict_ext;

														$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
														$sql_del .= " where ". $this->id_key."='".$id_row."' ";
														$image_ext = $q->select1($sql_del);
														$pict_ext_old = $image_ext[$this->cols[$k]['name']];
														@unlink($path.$id_row.$pict_ext_old);
														@unlink($path.'pre'.$id_row.$pict_ext_old);
														copy($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'], $filename);
														list($w, $h) = custom_getimagesize($filename);
														$h = round(($h/($w/50)), 0);
														$w=50;
														resize_then_crop($filename,$path.'pre'.$id_row.$pict_ext,$w,$h,255,255,255);
													}
												}else{
														if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
															$sql_del .= " where ". $this->id_key."='".$id_row."' ";
															$image_ext = $q->select1($sql_del);
															$pict_ext = $image_ext[$this->cols[$k]['name']];
															$path = $this->cols[$k]['param1'];
															$filename = $path.$id_row.$pict_ext;
															if(file_exists($filename)){
																@unlink($filename);
																@unlink($path.'pre'.$id_row.$pict_ext);
															}
														}
												}


											if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
												$sql .= " ".$this->cols[$k]['name']." = '".$pict_ext."',";
											}else{
													if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql .= " ".$this->cols[$k]['name']." = '',";
													}
											}

											break;
									case 'file_id' :
												if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
													$file_ext='';
													if(is_file($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'])){
														$str_temp = $_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['name'];
														for($i=strlen($str_temp);$i>0;$i--){
															if($str_temp[$i] == '.') break;
														}
														$file_ext = substr($str_temp,$i);
													}
													if(!empty($file_ext)){
														$path = $this->cols[$k]['param1'];
														$filename = $path.$id_row.$file_ext;
														$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
														$sql_del .= " where ". $this->id_key."='".$id_row."' ";
														$temp = $q->select1($sql_del);
														$file_ext_old = $temp[$this->cols[$k]['name']];
														@unlink($path.$id_row.$file_ext_old);
														copy($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'], $filename);
													}
												}else{
														if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
															$sql_del .= " where ". $this->id_key."='".$id_row."' ";
															$temp = $q->select1($sql_del);
															$file_ext = $temp[$this->cols[$k]['name']];
															$path = $this->cols[$k]['param1'];
															$filename = $path.$id_row.$file_ext;
															if(file_exists($filename)){
																@unlink($filename);
															}
														}
												}
											if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
												$sql .= " ".$this->cols[$k]['name']." = '".$file_ext."',";
											}else{
													if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql .= " ".$this->cols[$k]['name']." = '',";
													}
											}

											break;
								case 'anyfile' :
												if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
													if(is_file($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'])){
														$file_name = $_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['name'];
														$path = $this->cols[$k]['param1'];
														$filename = $path.$file_name;
														$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
														$sql_del .= " where ". $this->id_key."='".$id_row."' ";
														$temp = $q->select1($sql_del);
														$file_old = $temp[$this->cols[$k]['name']];
														@unlink($path.$file_old);
														copy($_FILES['edit_'.$id_row.'_'.$this->cols[$k]['name']]['tmp_name'], $filename);
													}
												}else{
														if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql_del = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
															$sql_del .= " where ". $this->id_key."='".$id_row."' ";
															$temp = $q->select1($sql_del);
															$file_name = $temp[$this->cols[$k]['name']];
															$path = $this->cols[$k]['param1'];
															$filename = $path.$file_name;
															if(file_exists($filename)){
																@unlink($filename);
															}
														}
												}
											if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'yes'){
												$sql .= " ".$this->cols[$k]['name']." = '".$file_name."',";
											}else{
													if(get_param('editrad_'.$id_row.'_'.$this->cols[$k]['name']) == 'del'){
															$sql .= " ".$this->cols[$k]['name']." = '',";
													}
											}

											break;


						}
					}
				}
				$sql=substr($sql, 0, strlen($sql)-1);
				$sql .= " where ". $this->id_key."='".$id_row."' ";
				$q->exec($sql);
			}
			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end UPDATE///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////UP///////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "up_row" ){
			if(get_param('id_table_row')){
				$old_rank = $q->select1("select rank from ".$this->table." where ".$this->id_key."='".get_param('id_table_row')."' ");
				$sql = "select ".$this->id_key.", rank from ".$this->table;
				if($this->where) $sql .= " where ".$this->where." and rank > ".$old_rank['rank'];
				else $sql .= " where rank > ".$old_rank['rank'];
				$sql .=" order by rank";
				$new_rank = $q->select1($sql);

				if($new_rank != 0){
					$q->exec("update ".$this->table." set rank = ".$new_rank['rank']." where ".$this->id_key."='".get_param('id_table_row')."' ");
					$q->exec("update ".$this->table." set rank = ".$old_rank['rank']." where ".$this->id_key."='".$new_rank['id']."' ");
				}
			}
			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end UP///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////DOWN////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "down_row" ){
			if(get_param('id_table_row')){
				$old_rank = $q->select1("select rank from ".$this->table." where ".$this->id_key."='".get_param('id_table_row')."' ");
				//$new_rank = $q->select1("select ".$this->id_key.", rank from ".$this->table." where rank < ".$old_rank['rank']." order by rank desc");

				$sql = "select ".$this->id_key.", rank from ".$this->table;
				if($this->where) $sql .= " where ".$this->where." and rank < ".$old_rank['rank'];
				else $sql .= " where rank < ".$old_rank['rank'];
				$sql .=" order by rank desc";
				$new_rank = $q->select1($sql);

				if($new_rank != 0){
					$q->exec("update ".$this->table." set rank = ".$new_rank['rank']." where ".$this->id_key."='".get_param('id_table_row')."' ");
					$q->exec("update ".$this->table." set rank = ".$old_rank['rank']." where ".$this->id_key."='".$new_rank['id']."' ");
				}
			}

			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end DOWN///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////HIDE////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "hide_row" ){
			if(get_param('id_table_row')){
				$q->exec("update ".$this->table." set status=0  where ".$this->id_key."=".get_param('id_table_row') );
			}
			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end HIDE///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////SHOW////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "show_row" ){
			if(get_param('id_table_row')){
				$q->exec("update ".$this->table." set status=1  where ".$this->id_key."=".get_param('id_table_row') );
			}
			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end SHOW///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////DELETE//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(get_param('tmode') == "del" ){
			$id_to_edit = array();//?????? id ??????? ????? Delete
			foreach($_POST as $k=>$v){
				if (substr($k,0,9) == 'ch_action'){
					$id_to_edit[count($id_to_edit)] = substr($k, 9);
				}

			}
			foreach($id_to_edit as $id_row){
				if(!$id_row) continue;

				foreach($this->cols as $k=>$v){
					if($this->cols[$k]['edit']){
						switch($this->cols[$k]['coltype']){
								case 'image' :
									$sql = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
									$sql .= " where ". $this->id_key."='".$id_row."' ";
									$image_ext = $q->select1($sql);
									$pict_ext = $image_ext[$this->cols[$k]['name']];
									$path = $this->cols[$k]['param1'];
									$filename = $path.$id_row.$pict_ext;
									if(file_exists($filename)){
										@unlink($filename);
										@unlink($path.'pre'.$id_row.$pict_ext);
									}
									break;
								case 'file_id' :
									$sql = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
									$sql .= " where ". $this->id_key."='".$id_row."' ";
									$temp = $q->select1($sql);
									$file_ext = $temp[$this->cols[$k]['name']];
									$path = $this->cols[$k]['param1'];
									$filename = $path.$id_row.$file_ext;
									if(file_exists($filename)){
										@unlink($filename);
										//@unlink($path.'pre'.$id_row.$pict_ext);
									}
									break;
								case 'anyfile' :
									$sql = "select ".$this->cols[$k]['name']." from  ".$this->table."  ";
									$sql .= " where ". $this->id_key."='".$id_row."' ";
									$temp = $q->select1($sql);
									$file_ext = $temp[$this->cols[$k]['name']];
									$path = $this->cols[$k]['param1'];
									$filename = $path.$file_ext;
									if(file_exists($filename)){
										@unlink($filename);
										//@unlink($path.'pre'.$id_row.$pict_ext);
									}
									break;

						}
					}
				}

				$sql = "delete from  ".$this->table."  ";
				$sql .= " where ". $this->id_key."='".$id_row."' ";
				$q->exec($sql);






			}
			$_POST['tmode'] = '';
		}
/////////////////////////////////////////////////////////end DELETE///////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////// DRAW////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if(get_param('tmode') != "edit" && get_param('tmode') != "delete" && get_param('tmode') != "add" && get_param('tmode') != "add_row"){
			echo '<h2>'.$this->caption.'</h2>';
			$sql = "select ";
			foreach($this->cols as $k=>$v){
				if($this->cols[$k]['list']){
					if($this->cols[$k]['coltype'] == 'data'){
						$sql.= 'DATE_FORMAT( '.$this->cols[$k]['name'].' , \'%d.%m.%Y\' ) as '.$this->cols[$k]['name'];
					}else{
						$sql.=$this->cols[$k]['name'];
					}
					$sql.=', ';
				}
			}
			if($this->is_rank){
				$sql.= ' rank, ' ;
			}
			if($this->is_status){
				$sql.= ' status, ' ;
			}
			$sql.= $this->id_key;

			$page_size = $this->page_size;
			$page_name = 'page';
			$q_num = "select count(".$this->id_key.") as number from ".$this->table;
			if($this->where) $q_num .= " where ".$this->where;
			$num = $q->select1($q_num);

			$total_number = $num['number'];
			$totalpages = (int)(($total_number -1)/$page_size);
			if( $totalpages < (int)get_param($page_name) )
				$_GET[$page_name] = $totalpages;

			$sql.= " from ".$this->table;
			if($this->where) $sql .= " where ".$this->where;

			if($this->is_rank){
				$sql.= " order by rank desc ";
			}
			$sql.= " LIMIT ".((get_param($page_name,0))*$page_size).", ".$page_size;
			$data = $q->select($sql);
			draw_pages($page_size , $total_number, $page_name, "" , '');

			echo '<form method="POST" name="main_form" id="main_form" >
			<input type="hidden" name="tmode" id="tmode" value="">
			<input type="hidden" name="id_table_row" id="id_table_row" value="">';

			echo '<table ><tr>';
			if($this->is_add){
				echo '<td>
				<input type="button"  class="btn" value="Add" onclick="';
				echo 'ActionTable(\'add\')"></td>';
			}
			if($this->is_edit){
				echo '<td>
				<input type="button" class="btn" value="Edit" onclick="do_edit();"></td>';
			}
			if($this->is_del){
				echo '<td>
				<input type="button" class="btn" value="Delete" onclick="do_del();"></td>';
			}
			echo '</tr></table>';

			echo '<table class="simptable"><tr class="head_tr">';
			if($this->is_edit || $this->is_del)
				echo '<td align="center"><div onclick="Invert()" style="cursor:hand"><b>*</b></div></td>';
			echo '<td>'.$this->id_key.'</td>';
			foreach($this->cols as $k=>$v){
				if($this->cols[$k]['list']){
					echo '<td>'.$this->cols[$k]['caption'].'</td>';
				}
			}
			if($this->is_rank  || $this->is_status || sizeof($this->actions)>0 ){
				echo '<td>????????</td>';
			}
			echo '</tr>';
			$for_script = '';
			if($this->is_rank){
				$q_min = "select min(rank) as min from ".$this->table;
				if($this->where) $q_min .= " where ".$this->where;
				$min = $q->select1($q_min);
				$q_max = "select max(rank) as max from ".$this->table;
				if($this->where) $q_max .= " where ".$this->where;
				$max = $q->select1($q_max);
			}
			$nrow=1;
			foreach($data as $row){
				$for_script .= $row[$this->id_key].'|';
				echo '<tr ';
				if($nrow==1){
					$nrow =2;
					echo 'class="row1"';
				}else{
					$nrow =1;
					echo 'class="row2"';
				}
				echo ' >';
				echo '<td valign="top" ><input type="checkbox" name="ch_action'.$row[$this->id_key].'" id="ch_action'.$row[$this->id_key].'"></td>';
				echo '<td valign="top" >'.$row[$this->id_key].'</td>';
				foreach($this->cols as $k=>$v){
					if($this->cols[$k]['list']){
						switch($this->cols[$k]['coltype']){
							case 'text' : echo '<td valign="top" >'.$row[$this->cols[$k]['name']];
										if(empty($row[$this->cols[$k]['name']]))
											echo '&nbsp;';
										echo '</td>';
										break;
							case 'textarea' : echo '<td valign="top" >'.$row[$this->cols[$k]['name']];
										if(empty($row[$this->cols[$k]['name']]))
											echo '&nbsp;';
										echo '</td>';
										break;
							case 'fck' : echo '<td valign="top" >'.$row[$this->cols[$k]['name']];
										if(empty($row[$this->cols[$k]['name']]))
											echo '&nbsp;';
										echo '</td>';
										break;

							case 'image' :
										echo '<td>';
										$path = $this->cols[$k]['param1'];
										$filename = $path.'pre'.$row[$this->id_key].$row[$this->cols[$k]['name']];
										if(file_exists($filename)){
											echo '<img src="'.$filename.'">';
										}else{
											echo '???';
										}
										echo '</td>';
										break;

							case 'file_id' :
										echo '<td>';
										$path = $this->cols[$k]['param1'];
										$filename = $path.$row[$this->id_key].$row[$this->cols[$k]['name']];
										if(file_exists($filename)){
											echo '????('.$row[$this->cols[$k]['name']].')';
										}else{
											echo '???';
										}
										echo '</td>';
										break;
							case 'anyfile' :
										echo '<td>';
										$path = $this->cols[$k]['param1'];
										$filename = $path.$row[$this->cols[$k]['name']];
										if(file_exists($filename) && !empty($row[$this->cols[$k]['name']])){
											echo $row[$this->cols[$k]['name']];
										}else{
											echo '??? ?????';
										}
										echo '</td>';
										break;
							case 'select' : echo '<td valign="top" >'.$this->cols[$k]['param1'][$row[$this->cols[$k]['name']]];
									/*	if(empty($row[$this->cols[$k]['name']]))
											echo '&nbsp;';*/
										echo '</td>';
										break;
							case 'data' : echo '<td valign="top" >'.$row[$this->cols[$k]['name']];
									/*	if(empty($row[$this->cols[$k]['name']]))
											echo '&nbsp;';*/
										echo '</td>';
										break;

						}
					}
				}
				if($this->is_rank  || $this->is_status || sizeof($this->actions) > 0){
					echo '<td valign="top" >';
					if($this->is_status){
						if($row['status'] == 1)
							echo '<div onclick="an_action(\''.$row[$this->id_key].'\', \'hide_row\')" style="cursor:hand"><img src="/admin/pic/noactive.bmp" align="left" alt="??????" border="0"></div>';
						else
							echo '<div onclick="an_action(\''.$row[$this->id_key].'\', \'show_row\')" style="cursor:hand"><img src="/admin/pic/activate.bmp" align="left" alt="????????"></div>';
					}

					if($this->is_rank){
						if($max['max'] != $row['rank'])
							echo '<div onclick="an_action(\''.$row[$this->id_key].'\', \'up_row\')" style="cursor:hand"><img src="/admin/pic/up.gif" alt="????" align="left"></div>';
						if($min['min'] != $row['rank'])
							echo '<div onclick="an_action(\''.$row[$this->id_key].'\', \'down_row\')" style="cursor:hand"><img src="/admin/pic/down.gif" alt="????" align="left"></div>';
					}
					$page_name_str = basename($PHP_SELF);
					foreach($this->actions as $act){
						echo '<br><a href="'.$act['name'].'.php?page_back='.$page_name_str.'&this_block_id='.get_param('this_block_id').'&'.$act['id_name'].'='.$row[$this->id_key].'">'.$act['caption'].'</a>';
					}

					echo '</td>';
				}
				echo '</tr>';
			}
			echo '</table>';


			echo '<table><tr>';
			if($this->is_add){
				echo '<td>
				<input type="button"  class="btn" value="Add" onclick="';
				echo 'ActionTable(\'add\')"></td>';
			}
			if($this->is_edit){
				echo '<td>
				<input type="button" class="btn" value="Edit" onclick="do_edit();"></td>';
			}
			if($this->is_del){
				echo '<td>
				<input type="button" class="btn" value="Delete" onclick="do_del();"></td>';
			}
			echo '</tr></table></form>';




			echo "
			<script>
main_form = document.forms.main_form;
function Invert(){
";

$data= explode('|' ,$for_script);
foreach($data as $row){
	if($row){
		echo 'if(main_form.ch_action'.$row.'.checked) main_form.ch_action'.$row.'.checked= false;
				else main_form.ch_action'.$row.'.checked = true;';
	}
}
echo '}';

if($this->is_edit){
	echo 'function do_edit(){';
	echo 'flag = 0;';
	$data= explode('|' ,$for_script);
	foreach($data as $row){
		if($row){
			echo 'if(main_form.ch_action'.$row.'.checked) flag=1;';
		}
	}
	echo 'if(flag==0){
		alert(\'No rows checked\');
		return;
	}';
	echo 'ActionTable(\'edit\'); }';
}
if($this->is_del){
	echo 'function do_del(){';
	echo 'flag = 0;';
	$data= explode('|' ,$for_script);
	foreach($data as $row){
		if($row){
			echo 'if(main_form.ch_action'.$row.'.checked) flag=1;';
		}
	}
	echo 'if(flag==0){
		alert(\'No rows checked\');
		return;
	}';
	echo 'ActionTable(\'del\'); }';
}












echo "
function an_action(id,act){
	main_form.id_table_row.value = id;
	main_form.tmode.value = act;
	main_form.submit();
}
function ActionTable(act){
	if(act == 'del'){
		if(!confirm('Delete checked rows?') ) return;
	}
	main_form.tmode.value = act;
	main_form.submit();
}
</script>
			";
		}
	}
	/////////////////////////////////////////////////////////end  DRAW///////////////////////////////////////////////////////////////////


}