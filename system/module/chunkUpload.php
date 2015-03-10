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

class chunkUpload {

	/**
	 * Upload Live Connection
	 *
	 * @var mixen
	 * @access protected
	 */
	
	private static $request = array();
	private static $config = array();
	private static $error = false;

	// --------------------------------------------------------------------

	/**
	 * check request if chunky
	 *
	 * @access	public
	 * @return	mixen
	 */
	public static function chunky(){
		return isset($_REQUEST['flowFilename']);
	}

	// --------------------------------------------------------------------

	/**
	 * initialize chunk upload class after check if it chunky
	 *
	 * @access	public
	 * @param   array config
	 * @return	mixen
	 */
	public static function init($config = array()){
		self::$request = array(
			"filename" 			=> isset($_REQUEST['flowFilename'])?$_REQUEST['flowFilename']:null,
			"totalSize" 		=> isset($_REQUEST['flowTotalSize'])?$_REQUEST['flowTotalSize']:null,
			"identifier" 		=> isset($_REQUEST['flowIdentifier'])?$_REQUEST['flowIdentifier']:null,
			"relativePath" 		=> isset($_REQUEST['flowRelativePath'])?$_REQUEST['flowRelativePath']:null,
			"totalChunks" 		=> isset($_REQUEST['flowTotalChunks'])?$_REQUEST['flowTotalChunks']:null,
			"chunkSize" 		=> isset($_REQUEST['flowChunkSize'])?$_REQUEST['flowChunkSize']:null,
			"chunkNumber" 		=> isset($_REQUEST['flowChunkNumber'])?$_REQUEST['flowChunkNumber']:null,
			"currentChunkSize" 	=> isset($_REQUEST['flowCurrentChunkSize'])?$_REQUEST['flowCurrentChunkSize']:null,
			"file" 				=> isset($_FILES['file'])?$_FILES['file']:null,
		);

		self::$config = array();
		self::$config['tempDir'] = isset($config['tempDir'])?$config['tempDir']:"temp";
		self::$config['hashChunk'] = isset($config['hashChunk'])?$config['hashChunk']:true;
		self::$config['deleteChunksOnSave'] = isset($config['deleteChunksOnSave'])?$config['deleteChunksOnSave']:true;
	}

	// --------------------------------------------------------------------

	/**
	 * start upload process return false if error or chunk and true if completed
	 *
	 * @access	public
	 * @param	string 	Upload File Destination
	 * @return	mixen
	 */
    public static function GET(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (self::checkChunk()) {
                header("HTTP/1.1 200 Ok");
            } else {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
        }
    }
	public static function upload($destination,$agnoreGET=false){
		
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if(!$agnoreGET){
    		    if (self::checkChunk()) {
    		        header("HTTP/1.1 200 Ok");
    		    } else {
    		        header("HTTP/1.1 404 Not Found");
    		        return false;
    		    }
            }
		} else {
		    if (self::validateChunk()) {
		        self::saveChunk();
		    } else {
		        // error, invalid chunk upload request, retry
		        header("HTTP/1.1 400 Bad Request");
		        return false;
		    }
		}

		if (self::validateFile() && self::save($destination)) {
			if (1 == mt_rand(1, 100)) { // deleting not important chunks
				self::pruneChunks();
			}

		    return true;// final chunk and complete
		} else {
		    return false;// not final chunk continue upload
		}
	}

	// --------------------------------------------------------------------

	/**
	 * request status
	 *
	 * @access	public
	 * @return	mixen
	 */
    public static function status(){
    	$cls = new stdClass();
    	$cls->status = (!self::$error)?true:false;
    	if($cls->status){
    		self::$error = "uploading chunk";
    	}
    	$cls->error = self::$error;
    }
	// --------------------------------------------------------------------

	/**
	 * chunk upload path
	 *
	 * @access	private
	 * @param	string 	chunk file index
	 * @return	mixen
	 */
    private static function getChunkPath($index){
    	if(self::$config['hashChunk'])$identifier = sha1(self::$request['identifier']);
    	else $identifier = self::$request['identifier'];
        return self::$config['tempDir'].DIRECTORY_SEPARATOR.$identifier.'_'.$index;
    }

	private static function checkChunk(){
		return file_exists(self::getChunkPath(self::$request['chunkNumber']));
	}

    private static function validateChunk(){
        $file = self::$request['file'];

        if (!$file) {
        	return false;
        }

        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            return false;
        }

        if (self::$request['currentChunkSize'] != $file['size']) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        return true;
    }

    private static function saveChunk(){
        $file = self::$request['file'];
        return move_uploaded_file($file['tmp_name'], self::getChunkPath(self::$request['chunkNumber']));
    }

    private static function validateFile()
    {
        $totalChunks = self::$request['totalChunks'];
        $totalChunksSize = 0;

        for ($i = 1; $i <= $totalChunks; $i++) {
            $file = self::getChunkPath($i);
            if (!file_exists($file)) {
                return false;
            }
            $totalChunksSize += filesize($file);
        }

        return self::$request['totalSize'] == $totalChunksSize;
    }

    private static function save($destination){
        $fh = fopen($destination, 'wb');
        if (!$fh) {
            self::$error = 'failed to open destination file: '.$destination;
            exit;
        }

        if (!flock($fh, LOCK_EX | LOCK_NB, $blocked)) {
            // @codeCoverageIgnoreStart
            if ($blocked) {
                // Concurrent request has requested a lock.
                // File is being processed at the moment.
                // Warning: lock is not checked in windows.
                return false;
            }
            // @codeCoverageIgnoreEnd

            self::$error = 'failed to lock file: '.$destination;
            exit;
        }

        $totalChunks = self::$request['totalChunks'];

        try {

            for ($i = 1; $i <= $totalChunks; $i++) {
                $file = self::getChunkPath($i);
                $chunk = fopen($file, "rb");

                if (!$chunk) {
                    self::$error = 'failed to open chunk: '.$file;
                    exit;
                }

                stream_copy_to_stream($chunk, $fh);
                fclose($chunk);
            }
        } catch (\Exception $e) {
            flock($fh, LOCK_UN);
            fclose($fh);
            self::$error = $e;
            exit;
        }

        if (self::$config['deleteChunksOnSave']) {
            self::deleteChunks();
        }

        flock($fh, LOCK_UN);
        fclose($fh);

        return true;
    }

    private static function deleteChunks(){
        $totalChunks = self::$request['totalChunks'];

        for ($i = 1; $i <= $totalChunks; $i++) {
            $path = self::getChunkPath($i);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private static function pruneChunks($expirationTime = 172800){
    	$chunksFolder = self::$config['tempDir'];
        $handle = opendir($chunksFolder);

        if (!$handle) {
            self::$error = 'failed to open folder: '.$chunksFolder;
        }

        while (false !== ($entry = readdir($handle))) {
            if ($entry == "." || $entry == "..") {
                continue;
            }

            $path = $chunksFolder.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($path)) {
                continue;
            }

            if (time() - filemtime($path) > $expirationTime) {
                unlink($path);
            }
        }

        closedir($handle);
    }


}

/* End of file chunkUpload.php */
/* Location: ./system/module/chunkUpload.php */