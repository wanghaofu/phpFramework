<?php
chdir ( '../' );
include ('./class/global.php');
$swfData = file_get_contents ( "http://stra.365ub.com/test/calculagraph.swf" );
header('Content-type: application/x-shockwave-flash');
$obj= new SWFEditor ();
$obj->input ( $swfData );

// de ( $swf->swfInfo () );

// de ( $s = $swf->getTagList () );
// for($key = 0; $key < 100; $key ++) 
// {
// 	echo $key;
// 	de ( $swf->getTagDetail ( $key ) );
// }

// executeCallback
// totalSeconds


$variables = array();

// foreach ($key_value_list as $key_value) {
// 	list($key, $value) = explode('=', $key_value, 2);
// 	$variables[$key] = $value;
// }
$variables['totalSeconds'] = 1900050;

if ($obj->setActionVariables($variables) == false) {
	echo "failed\n";
	exit(1);
}

// for($key = 0; $key <20; $key ++)
// 	{
// 		echo $key;
// 		de ( $obj->getTagDetail ( $key ) );
// 	}


echo $obj->output();

// $swf->input($swf);

// function input(string swddata) return true/false;
// function output() return string swfdata;
// function swfInfo() return ; // print swfInfo
// function getTagList();
// return array(array('tag'=>long,
// 'length'=>long,
// 'detail'=>bool),
// ...)
// function getTagDetail(integer seqno);
// return array(...); image_id とかそれ系
// function getJpegData(integer image_id)
// return string jpegdata;
// function getJpegAlpha(integer image_id)
// return string alphadata;
// function replaceJpegData(integer image_id, string jpegdata
// [,string alphadata])
// return true/false;
// function getPNGData(integer image_id)
// return string pngdata;
// function replacePNGData(integer image_id, string pngdata)
// return true/false;
// function replaceGIFData(integer image_id, string gifdata)
// return true/false;
// function getSoundData(integer sound_id) // getMP3Data は廃止。
// return string sounddata;
// function replaceMLDData(integer sound_id, string mlddata)
// return true/false;
// function getEditString(string [variable_name|edit_id])
// return text;
// function replaceEditString(string [variable_name|edit_id],
// string text)
// return true/false;
// ※ 誤って {get|replace}EditTextString と記述していました。すみません。
// ※ Flash ver 6 以降は UTF-8 ですが、Flash Lite は ver 4 相当なので
// ※ CP932(SJIS-Win)エンコーディングです。アプリ側でコード変換して下さい。
// function getHeaderInfo() return array('compress'=>...,
// 'version'=>...);
// function setHeaderInfo(array('compress'=>..., 'version'=>...))
// return true/false; function applyShapeMatrixFactor(shape_id, scale_x,
// scale_y, radian,
// trans_x, tranx_y)
// return true/false;
// function applyShapeRectFactor(shape_id, scale_x, scale_y,
// trans_x, tranx_y)
// return true/false;
// function setShapeAdjustMode(mode)
// mode: SWFEditor::SHAPE_BITMAP_MATRIX_RESCALE - 枠の大きさを変えず画像のスケールで調整
// mode: SWFEditor::SHAPE_BITMAP_RECT_RESIZE - 画像の大きさに合わせて枠のサイズ変更
// mode: SWFEditor::SHAPE_BITMAP_TYPE_TILLED - 画像をタイル状に表示 function
// getShapeIdListByBitmapRef($image_id)
// function getBitmapSize($image_id)
// PHP extension API (開発中)
// function getMovieHeaderInfo()
// return array('frame_size'=>
// array('x_min' =>..,'x_max' => ..,
// 'y_min' =>..,'y_max' => ..);
// 'frame_rate'=>...,
// 'frame_count'=>...);
// function setMovieHeaderInfo(array('frame_size'=>
// array('x_min' =>..,'x_max' => ..,
// 'y_min' =>..,'y_max' => ..);
// 'frame_rate'=>...,
// 'frame_count'=>...);
// return true/false;
// function getTagData(integer seqno);
// return string tagdata;
// function replaceTagData(integer seqno, string tag_data, [unsigned short
// new_id]);
// return true/false;
// function replaceMP3Data(integer sound_id, string mp3data,
// integer samples)
// return true/false; •PHP extension API (未実装)
// function getFontData($font_id) return $font_data;
// function replaceFontData($font_id, $font_data)
// return true/false;
// function replaceShapeBitmapGeometryByImageId($image_id, $x, $y, $witdh,
// $height);
// return true/false
// function getSymbolSWF(string symbol_name)
// return swfdata;

// function replaceSymbolSWF(string symbol_name, string swfdata)
// return true/false;
// function getActionData(integer seqno); // 微妙
// function disasmActionData(string actiondata);
// return Array(Array('op'=>$code,
// 'data'=>$data)
// );
// function asmActiondata(Array(Array('op'=>$code, 'data'=>$data)));
// return action_data;
// function replaceActionData(integer seqno, string actiondata);
// return true/false;
// function getAlphaDataFromGIFData(string gifdata)
// return alphadata;
// function replaceActionVarData(string var_name, string var_data);
// return true/false;

?>