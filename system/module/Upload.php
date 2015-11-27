<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Upload Class
 *
 * This class Manage Upload Connections & Queries 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Upload{

	/**
	 * Upload Live Connection
	 *
	 * @var mixen
	 * @access protected
	 */
	protected static $db = null;

	// --------------------------------------------------------------------

	/**
	 * Upload push
	 *
	 * @access	public
	 * @param	array 	Upload Config
	 * @return	mixen
	 */
	public static function push($arr=array()){

		//ini_set( 'memory_limit', '200M' );
		//ini_set('upload_max_filesize', UPLOAD_MAX_SIZE.'M');
		//ini_set('post_max_size', UPLOAD_MAX_SIZE.'M');
		//ini_set('max_input_time', 600);  
		//ini_set('max_execution_time', 600);
		/*
		$arr = array(
			'name' => 'file',
		//	'allowed' => 'JPG',
		//	'folder' => 'images',
		//	'tags' => 'image',
			
		//	'resize' => array(400),
		//	'thumbs' => true,
		//	'target' => 'filename',
		//	'library' => 'library_path',
		);*/
	
		global $config;

		if(!isset($arr['name']))$arr['name'] = 'file';

		Module::import('chunkUpload');
		if(chunkUpload::chunky()){
			$tempDir = "{$config['assets']}/library/temp";
			if(!is_dir($tempDir)){
				mkdir($tempDir,0775,true);
			}
			/*
			if (!file_exists($tempDir)) {
				mkdir($tempDir);
			}*/
			chunkUpload::init(array('tempDir'=>$tempDir));// set temp and things
			chunkUpload::GET();
		}

		// Redefine config if not exist
		if(!isset($config['upload'])){
			$config['upload'] = array();
		}else{
			if(!is_array($config['upload'])){
				$config['upload'] = array();
			}
		}
		if(!isset($config['upload']['public'])){
			$config['upload']['public'] = "JPG,JPEG,PNG,GIF,MP3,WAV,PDF,MP4";
		}
		if(!isset($config['upload']['private'])){
			$config['upload']['private'] = "JPG,JPEG,PNG,GIF,MP3,M4A,OGG,WAV,MP4,M4V,MOV,WMV,AVI,MPG,OGV,3GP,3G2,PDF,ODT,DOC,DOCX,XLS,XLSX,PPT,PPTX,PPS,PPSX,RAR,ZIP,GZ,TAR";
		}
		if(!isset($config['upload']['maxsize'])){
			$config['upload']['maxsize'] = 8;
		}
		if(!isset($config['upload']['thumbnails'])){
			$config['upload']['thumbnails'] = array("sm"=>300);
		}

		$cls = new stdClass();
		
		Module::import("User");
		if(User::hasRule('upload-private-ext')){
			$allowed = explode(',',strtoupper($config['upload']['private']));
			$type = 'UPLOAD_PRIVATE_EXT';
		}else{
			$allowed = explode(',',strtoupper($config['upload']['public']));
			$type = 'UPLOAD_PUBLIC_EXT';
		};
		
		if(isset($arr['allowed'])){
			$al = true;
			$al1 = explode(',', strtoupper($arr['allowed']));
			$ext;
			foreach ($al1 as $v){
				if(!in_array(strtoupper($v), $allowed)){
					$al=false;
					$ext = $v;
					break;
				}
			}
			if(!$al){
				$cls->error = "you have to enable extension '{$ext}' in {$type}";
				$cls->status = false;
				return $cls;
			}
			$allowed = $al1;
			$type = "UPLOAD_USER_DEFINED_EXT";
		}

		if(!isset($_FILES[$arr['name']]['name'])){
			$cls->error = "you forget to send _FILE[{$arr['name']}]";
			$cls->status = false;
			return $cls;
		}
		
		$filename = $_FILES[$arr['name']]['name'];
		$ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
		if(!in_array($ext,$allowed) ) {
			$cls->error = "Extension {$ext} not allowed in {$type}";
			$cls->status = false;
			return $cls;
		}

		// check size
		if($_FILES[$arr['name']]["size"] > $config['upload']['maxsize']*1024*1024){
			$cls->error = "you can't Upload file's bigger than ".$config['upload']['maxsize']."MB";
			$cls->status = false;
			return $cls;
		}

		if(!isset($arr['folder'])){
			if(in_array($ext,explode(',',"JPG,JPEG,PNG,GIF,SVG")))$arr['folder'] = 'images';
			else if(in_array($ext,explode(',',"MP3,MP4,MOV,WAV,AVI,FLV,ASF,OGG")))$arr['folder'] = 'media';
			else if(in_array($ext,explode(',',"PDF,DOC,DOCX,XLS,XLSX")))$arr['folder'] = 'documents';
			else if(in_array($ext,explode(',',"ZIP,RAR,TAR.GZ")))$arr['folder'] = 'compressed';
			else $arr['folder'] = 'others';
		}

		$year = date("Y");
		$month = date("m");

		if(!isset($arr['library'])){
			$lib = "{$config['assets']}/library/{$arr['folder']}/{$year}/{$month}/";
		}else{
			$lib = $arr['library'];
		}

		if(!is_dir($lib)){
			mkdir($lib,0775,true);
		}

		$tempFile = $_FILES[$arr['name']]['tmp_name'];
		$target = $lib.(isset($arr['target'])?$arr['target']:String::randomId(12));
		$targetFile =  "{$target}.{$ext}";

		// upload type
		if(chunkUpload::chunky()){
			if(!chunkUpload::upload($targetFile,true)){
				return chunkUpload::status();
			};
		}else{
			if(!move_uploaded_file($tempFile,$targetFile)){
				$cls->error = "Error in server Check folder Permissions in path : {$lib}";
				$cls->status = false;
				return $cls;
			};
		}

		$file = array();
		$file['original'] = $targetFile;

		if(in_array($ext,explode(',',"JPG,JPEG,PNG,GIF"))){
			if(isset($arr['resize'])){
				Image::resize(array(
					'source' 	=> $targetFile,
					'target' 	=> $targetFile,
					'size' 		=> $arr['resize']
				));
			}
			if(isset($arr['thumbs'])){
				if(is_array($arr['thumbs']))$tbs = $arr['thumbs'];
				else $tbs = $config['upload']['thumbnails'];

				if(isset($tbs['original'])){
					Image::resize(array(
						'source' 	=> $targetFile,
						'target' 	=> $targetFile,
						'size' 		=> $arr['thumbs']['original']
					));
					unset($tbs['original']);
				}

				foreach($tbs as $type => $size){
					$rz = Image::resize(array(
						'source' 	=> $targetFile,
						'target' 	=> "{$target}-{$type}.{$ext}",
						'size' 		=> $size
					));
					$file[$type] = $rz->target;//"{$target}-{$type}.{$ext}";
				}
			}
		}

		/*
		Create Additional Upload Features by array and register

		if(in_array($ext,explode(',',"MP3")) && AUDIO_PEAKS == true){//sound optimize

			$dir = dirname(dirname(dirname(__FILE__)));
			self::shell_execute("lame","{$dir}/{$target}.{$ext} -m m -S -f -b 16 --resample 8 {$target}-temp.mp3");
			self::shell_execute("lame","-S --decode {$target}-temp.mp3 {$target}.wav");
			self::shell_execute("wav2json","{$target}.wav -s 400 --channels left -n -o {$target}.json");
			unlink("{$target}-temp.mp3");
			unlink("{$target}.wav");
			$file['peaks'] = "{$target}.json";
		}

		/*
		if(in_array($ext,explode(',',"MP4,MOV,AVI,FLV,ASF")) && VIDEO_FIT != false){
			$dir = dirname(dirname(dirname(__FILE__)));
			$file[test] = "{$dir}/{$target}.{$ext}";
			
			//thumb
			if(in_array('THUMBS', explode(',', VIDEO_FIT))){
				self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -ss 5 -vframes 1 -r 1 -vf scale=854x480 -f image2 {$target}_thumb1.jpg");
				self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -ss 15 -vframes 1 -r 1 -vf scale=854x480 -f image2 {$target}_thumb2.jpg");
				self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -ss 30 -vframes 1 -r 1 -vf scale=854x480 -f image2 {$target}_thumb3.jpg");
				self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -ss 60 -vframes 1 -r 1 -vf scale=854x480 -f image2 {$target}_thumb4.jpg");
				$file['thumb1'] = "{$target}_thumb1.jpg";
				$file['thumb2'] = "{$target}_thumb2.jpg";
				$file['thumb3'] = "{$target}_thumb3.jpg";
				$file['thumb4'] = "{$target}_thumb4.jpg";
			}
			if(in_array('360p', explode(',', VIDEO_FIT))){
				if(in_array('MP4', explode(',', VIDEO_FIT))){
					self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -vcodec libx264 -pix_fmt yuv420p -vprofile high -preset fast -b:v 1000k -maxrate 1000k -bufsize 1000k -vf scale=480x320 -threads 0 -acodec libvo_aacenc -b:a 128k {$target}_360p.mp4");
					$file['360p'] = "{$target}_360p.mp4";
				}
			}
			if(in_array('480p', explode(',', VIDEO_FIT))){
				if(in_array('MP4', explode(',', VIDEO_FIT))){
					self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -vcodec libx264 -pix_fmt yuv420p -vprofile high -preset fast -b:v 2500k -maxrate 2500k -bufsize 2500k -vf scale=854x480 -threads 0 -acodec libvo_aacenc -b:a 128k {$target}_480p.mp4");
					$file['480p'] = "{$target}_480p.mp4";
				}
			}
			if(in_array('720p', explode(',', VIDEO_FIT))){
				if(in_array('MP4', explode(',', VIDEO_FIT))){
					self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -vcodec libx264 -pix_fmt yuv420p -vprofile high -preset fast -b:v 5000k -maxrate 5000k -bufsize 5000k -vf scale=1280x720 -threads 0 -acodec libvo_aacenc -b:a 384k {$target}_720p.mp4");
					$file['720p'] = "{$target}_720p.mp4";
				}
			}
			if(in_array('1080p', explode(',', VIDEO_FIT))){
				if(in_array('MP4', explode(',', VIDEO_FIT))){
					self::shell_execute("ffmpeg"," -y -i {$dir}/{$target}.{$ext} -vcodec libx264 -pix_fmt yuv420p -vprofile high -preset fast -b:v 8000k -maxrate 8000k -bufsize 8000k -vf scale=1920x1080 -threads 0 -acodec libvo_aacenc -b:a 384k {$target}_1080p.mp4");
					$file['1080p'] = "{$target}_1080p.mp4";
				}
			}
		}
		*/
		if(!isset($arr['tags'])){
			$arr['tags'] = array();
		}else{
			if(!is_array($arr['tags'])){
				$arr['tags'] = explode(",", $arr['tags']);
			}
		}
		array_push($arr['tags'], $arr['folder']);
		array_push($arr['tags'], $ext);

		if(!isset($arr['author'])){
			$user = User::info();
			$arr['author'] = $user->status?$user->data[0]['id']:0;
		}
		
		Module::import("Query");
		$q = Query::set('library',array(
			'data' 		=> array(
				'name' 		=> $filename,
				'path' 		=> json_encode($file),//serialize
				'updated' 	=> time(),
				'extension' => $ext,
				'size' 		=> File::format(filesize($file['original'])),
				'tags' 		=> implode(",", $arr['tags']),
				'author' 	=> $arr['author'],
			//	'taxonomy' 	=> isset($arr['taxonomy'])?$arr['taxonomy']:''
			)
		));
		if($q->status){
			$cls->status = true;
			$cls->file = $file;
			$cls->path = json_encode($file);
		}else{
			$cls = $q;
		}
		return $q;
	}

	/*
	create custom php extensions instead

	public static function shell_execute($app,$code){
		$output = array();
		exec('`/usr/bin/which /usr/local/bin/'.$app.'` '.$code.' 2>&1', $output);
		return ($output);
	}
	*/




	// oid or apending in query apend to list
	// get thumb by default
	public static function link($arr=array()){
		/*
		$arr = array(
			"library" => [1,2,3],
			"taxonomy" => "",
			"objects" => [4,5,6]
		);
		*/
		if(!is_array($arr['library']))$arr['library'] = explode(",",$arr['library']);
		if(!is_array($arr['objects']))$arr['objects'] = explode(",",$arr['objects']);

		$append_count = 0;
		foreach($arr['objects'] as $linkId){
			foreach ($arr['library'] as $libId) {
				$sql = array(
					"data" => array(
						"oid" => $linkId,
						"rid" => $libId,
						"table" => "library",
					),
					"ignore" => true
				);
				if(isset($arr['taxonomy'])){
					$sql['data']['taxonomy'] = $arr['taxonomy'];
				}
				$q = Query::set("relations",$sql);
				if($q->status == true && $q->last != 0)$append_count++;
			}
		}
		return $append_count;
	}

	/**
	 * unset the relations
	 * @param  array  library or objects as inside array
	 * @return void
	 */
	public static function unlink($arr=array()){
		/*		
		$arr = array(
			"library" => [1,2,3],
			"taxonomy" => "",// tax to remove
			//"objects" => [4,5,6]
		);
		*/
		if(isset($arr['library'])){
			if(is_array($arr['library']))$arr['library'] = implode(",",$arr['library']);
			$sql = array(
				"where" => array(
					"rid" => ":in:".$arr['library'],
					"table" => "library"
				)
			);
			if(isset($arr['taxonomy'])){
				$sql['where']['taxonomy'] = $arr['taxonomy'];
			}
			Query::remove("relations",$sql);
		}
		if(isset($arr['objects'])){
			if(is_array($arr['objects']))$arr['objects'] = implode(",",$arr['objects']);
			Query::remove("relations",array(
				"where" => array(
					"oid" => ":in:".$arr['objects'],
					"table" => "library"
				)
			));
		}
	}

	/**
	 * get image from library
	 * @param  array  images id
	 * @return [type]      [description]
	 */
	public static function get($arr=array()){
		//$arr = array(
			//"library" => [1,2,3],
			//"taxonomy" => "data", // come with objects only
			//"objects" => [4,5,6],
			//"author" => ids
		//);
		
		// todo
		// meta , taxonomy alone .. or with library and author
		//

		if(isset($arr['library']) || isset($arr['author'])){
			$data = array(
				"where" => array()
			);
			if(isset($arr['library'])){
				if(is_array($arr['library']))$arr['library'] = implode(",",$arr['library']);
				$data['where']['id'] = ":in:".$arr['library'];
			}
			if(isset($arr['author'])){
				if(is_array($arr['author']))$arr['author'] = implode(",",$arr['author']);
				$data['where']['author'] = ":in:".$arr['author'];
			}


			return Query::get("library",$data);
		}

		if(isset($arr['objects'])){
			if(is_array($arr['objects']))$arr['objects'] = implode(",",$arr['objects']);
			$sql = array(
				"join" => array(
					"relations" => "relations.oid in ({$arr['objects']}) AND relations.table = 'library'"
				),
				"where" => array(
					"relations.rid" => "~library.id",//"~:like:terms.id",
				)
			);
			if(isset($arr['taxonomy'])){
				$sql['join']['relations'] .= " AND relations.taxonomy = '{$arr['taxonomy']}'";
			}
			//if(isset($arr['taxonomy']))$sql['where']['taxonomy'] = $arr['taxonomy'];
			return Query::get("library",$sql);
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Upload remove
	 *
	 * @access	public
	 * @param	array 	Library Ids
	 * @param	boolean is admin
	 * @return	mixen
	 */
	public static function remove($ids,$force=false){
		$cls = new stdClass();
		if(!isset($ids)){
			$cls->status = false;
			$cls->error = "id not set";
			$cls->code = 1;
			return $cls;			
		}
		if(!is_array($ids))$ids = array($ids);
		$ids = implode(",", $ids);

		Module::import("Query");
		if(!$force){
			Module::import("User");
			$user = User::info();
			if(!$user->status)return $user;
		}
		$where = array("id"=>":in:{$ids}");
		if(!$force)$where['author'] = $user->data[0]['id'];

		$q = Query::get("library",array("where"=>$where));
		foreach($q->data as $row){
			$data = json_decode($row['path']);
			foreach($data as $img)if(is_file($img))unlink($img);
		}

		return Query::remove("library",array("where"=>$where));
	}
}

/* End of file Upload.php */
/* Location: ./system/module/Upload.php */