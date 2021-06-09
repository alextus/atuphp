<?php
class ATU_File{
    public function __construct() {
		$this->value='';
		$this->mime_arr = array (
			'7z'  => 'application/x-7z-compressed',
			'ai'  => 'application/postscript',
			'amr'  => 'audio/amr',
			'avi'  => 'video/x-msvideo',
			'bin'	=>	'application/macbinary',
			'bmp'  => 'image/bmp',
			'bz'  => 'application/x-bzip2',
			'bz2'  => 'application/x-bzip2',
			'chm'=>'application/octet-stream',
			'class'	=>	'application/octet-stream',
			'css'  => 'text/css',
			'csv'	=>	'application/vnd.ms-excel',
			'doc' => 'application/vnd.ms-word',
			'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dll'	=>	'application/octet-stream',
			'eps' => 'application/postscript',
			'exe' => 'application/octet-stream',
			'flv'  => 'video/x-flv',
			'gif'  => 'image/gif',
			'gtar'	=>	'application/x-gtar',
			'gz'  => 'application/x-gzip',
			'gzip'  => 'application/gzip',
			'html' => 'text/html',
			'htm'  => 'text/html',
			'java' => 'text/x-java-source',
			'js'	=>	'application/x-javascript', 
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpe'	=>	'image/pjpeg',
			'json' => 'text/json',
			'log'	=>	'text/plain',
			'mp3'  => 'audio/mpeg',
			'mid'  => 'audio/midi',
			'midi'	=>	'audio/midi',
			'mkv'  => 'video/x-matroska',
			'mov'  => 'video/quicktime',
			'movie'	=>	'video/x-sgi-movie',
			'mp3'	=>	'audio/mpeg', 
			'mp4'  => 'video/mp4',
			'mpg'  => 'video/mpeg',
			'mpeg' => 'video/mpeg',
			'ogg'  => 'audio/ogg',
			'png'  => 'image/png',
			'ppt'  => 'application/vnd.ms-powerpoint', 
			'pdf'  => 'application/pdf', 
			'php'  => 'text/x-php', //	'application/x-httpd-php',
			'phps'	=>	'application/x-httpd-php-source',
			'ps'	=>	'application/postscript',
			'psd'	=>	'application/octet-stream',
			'py'  => 'text/x-python',
			'qt'	=>	'video/quicktime',
			'rb'  => 'text/x-ruby',
			'rar'  => 'application/x-rar',//application/vnd.rar'
			'rtf'  => 'text/rtf', 			
			'rm'	=>'application/vnd.rn-realmedia',
			'smi'	=>	'application/smil',
			'smil'	=>	'application/smil',
			'sh'  => 'text/x-shellscript',
			'shtml'	=>	'text/html',
			'so'	=>	'application/octet-stream',	
			'svg'	=>'image/svg+xml',		
			'swf'  => 'application/x-shockwave-flash',
			'sql'  => 'text/x-sql',
			'tar'  => 'application/x-tar',
			'tga'  => 'image/x-targa',
			'tgz'  => 'application/x-compressed',
			'torrent'	=>	'application/x-bittorrent',
			'ts'	=>	'video/MP2T',
			'tif'  => 'image/tiff',
			'tiff' => 'image/tiff',
			'txt'  => 'text/plain',
			'wav'  => 'audio/wav',
			'wma'  => 'audio/x-ms-wma',
			'xhtml'	=>	'application/xhtml+xml',
			'xht'	=>	'application/xhtml+xml',
			'xls'  => 'application/vnd.ms-excel', 
			'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xml'	=>	'text/xml',
			'xsl'	=>	'text/xml',
			'xz'  => 'application/x-xz', 
			''	=>	'',
			'zip'  => 'application/x-zip-compressed' //application/zip
		);
    }
	public function __call($function, $args){
		echo $function."<br/>";
		array_unshift($args, $this->value);
		$this->value = call_user_func_array($function, $args);
		return $this;
	}
	function test(){
		echo 'test';
		echo $this->value;
	}
	function testB($b){
		echo 'testB';
		return $this;
	}

    public function getFileType($filename){
		echo $filename;
        if((is_readable($filename) || file_exists($filename)) && is_file($filename) ){
            $this->fp = fopen($filename, 'rb');

			echo get_resource_type($this->fp)."<br/>";//file  stream
        }else{
            $errormessagelist = array();
            if (!is_readable($filename)) {
                $errormessagelist[] = '!is_readable';
            }
            if (!is_file($filename)) {
                $errormessagelist[] = '!is_file';
            }
            if (!file_exists($filename)) {
                $errormessagelist[] = '!file_exists';
            }
            if (empty($errormessagelist)) {
                $errormessagelist[] = 'fopen failed';
            }
            throw new getid3_exception('Could not open "'.$filename.'" ('.implode('; ', $errormessagelist).')');
        }
        $formattest = fread($this->fp, 32774);
		//echo  $formattest;
        $determined_format = $this->GetFileFormat($formattest, $filename);
		return $determined_format;
    

    }
    public function GetFileFormat(&$filedata, $filename='') {
		// this function will determine the format of a file based on usually
		// the first 2-4 bytes of the file (8 bytes for PNG, 16 bytes for JPG,
		// and in the case of ISO CD image, 6 bytes offset 32kb from the start
		// of the file).

		// Identify file format - loop through $format_info and detect with reg expr
		foreach ($this->GetFileFormatArray() as $format_name => $info) {
			// The /s switch on preg_match() forces preg_match() NOT to treat
			// newline (0x0A) characters as special chars but do a binary match
			if (!empty($info['pattern']) && preg_match('#'.$info['pattern'].'#s', $filedata)) {
				print_r($info);
			
			}
		}


		if (preg_match('#\\.mp[123a]$#i', $filename)) {
			// Too many mp3 encoders on the market put garbage in front of mpeg files
			// use assume format on these if format detection failed
			$GetFileFormatArray = $this->GetFileFormatArray();
			$info = $GetFileFormatArray['mp3'];
			$info['include'] = 'module.'.$info['group'].'.'.$info['module'].'.php';
			return $info;
		} elseif (preg_match('#\\.cue$#i', $filename) && preg_match('#FILE "[^"]+" (BINARY|MOTOROLA|AIFF|WAVE|MP3)#', $filedata)) {
			// there's not really a useful consistent "magic" at the beginning of .cue files to identify them
			// so until I think of something better, just go by filename if all other format checks fail
			// and verify there's at least one instance of "TRACK xx AUDIO" in the file
			$GetFileFormatArray = $this->GetFileFormatArray();
			$info = $GetFileFormatArray['cue'];
			$info['include']   = 'module.'.$info['group'].'.'.$info['module'].'.php';
			return $info;
		}

		return false;
	}
	/**
	 * Return array containing information about all supported formats.
	 *
	 * @return array
	 */
	public function GetFileFormatArray() {
		/**
		 *  $len = strlen($test);
    if ($len >= 2 && $test[0] == chr(0xff) && $test[1] == chr(0xd8)) {
        return 'image/jpeg';
    }
    if ($len >= 3 && substr($test, 0, 3) == 'GIF') {
        return 'image/gif';
    }
    if ($len >= 4 && substr($test, 0, 4) == "\x89PNG") {
        return 'image/png';
    }
    return 'application/octet-stream';
		 * 
		 */
		static $format_info = array();
		if (empty($format_info)) {
			$format_info = array(

				// Audio formats

				// AC-3   - audio      - Dolby AC-3 / Dolby Digital
				'ac3'  => array(
							'pattern'   => '^\\x0B\\x77',
							'group'     => 'audio',
							'module'    => 'ac3',
							'mime_type' => 'audio/ac3',
						),

				// AAC  - audio       - Advanced Audio Coding (AAC) - ADIF format
				'adif' => array(
							'pattern'   => '^ADIF',
							'group'     => 'audio',
							'module'    => 'aac',
							'mime_type' => 'audio/aac',
							'fail_ape'  => 'WARNING',
						),

/*
				// AA   - audio       - Audible Audiobook
				'aa'   => array(
							'pattern'   => '^.{4}\\x57\\x90\\x75\\x36',
							'group'     => 'audio',
							'module'    => 'aa',
							'mime_type' => 'audio/audible',
						),
*/
				// AAC  - audio       - Advanced Audio Coding (AAC) - ADTS format (very similar to MP3)
				'adts' => array(
							'pattern'   => '^\\xFF[\\xF0-\\xF1\\xF8-\\xF9]',
							'group'     => 'audio',
							'module'    => 'aac',
							'mime_type' => 'audio/aac',
							'fail_ape'  => 'WARNING',
						),


				// AU   - audio       - NeXT/Sun AUdio (AU)
				'au'   => array(
							'pattern'   => '^\\.snd',
							'group'     => 'audio',
							'module'    => 'au',
							'mime_type' => 'audio/basic',
						),

				// AMR  - audio       - Adaptive Multi Rate
				'amr'  => array(
							'pattern'   => '^\\x23\\x21AMR\\x0A', // #!AMR[0A]
							'group'     => 'audio',
							'module'    => 'amr',
							'mime_type' => 'audio/amr',
						),

				// AVR  - audio       - Audio Visual Research
				'avr'  => array(
							'pattern'   => '^2BIT',
							'group'     => 'audio',
							'module'    => 'avr',
							'mime_type' => 'application/octet-stream',
						),

				// BONK - audio       - Bonk v0.9+
				'bonk' => array(
							'pattern'   => '^\\x00(BONK|INFO|META| ID3)',
							'group'     => 'audio',
							'module'    => 'bonk',
							'mime_type' => 'audio/xmms-bonk',
						),

				// DSF  - audio       - Direct Stream Digital (DSD) Storage Facility files (DSF) - https://en.wikipedia.org/wiki/Direct_Stream_Digital
				'dsf'  => array(
							'pattern'   => '^DSD ',  // including trailing space: 44 53 44 20
							'group'     => 'audio',
							'module'    => 'dsf',
							'mime_type' => 'audio/dsd',
						),

				// DSS  - audio       - Digital Speech Standard
				'dss'  => array(
							'pattern'   => '^[\\x02-\\x08]ds[s2]',
							'group'     => 'audio',
							'module'    => 'dss',
							'mime_type' => 'application/octet-stream',
						),

				// DSDIFF - audio     - Direct Stream Digital Interchange File Format
				'dsdiff' => array(
							'pattern'   => '^FRM8',
							'group'     => 'audio',
							'module'    => 'dsdiff',
							'mime_type' => 'audio/dsd',
						),

				// DTS  - audio       - Dolby Theatre System
				'dts'  => array(
							'pattern'   => '^\\x7F\\xFE\\x80\\x01',
							'group'     => 'audio',
							'module'    => 'dts',
							'mime_type' => 'audio/dts',
						),

				// FLAC - audio       - Free Lossless Audio Codec
				'flac' => array(
							'pattern'   => '^fLaC',
							'group'     => 'audio',
							'module'    => 'flac',
							'mime_type' => 'audio/flac',
						),

				// LA   - audio       - Lossless Audio (LA)
				'la'   => array(
							'pattern'   => '^LA0[2-4]',
							'group'     => 'audio',
							'module'    => 'la',
							'mime_type' => 'application/octet-stream',
						),

				// LPAC - audio       - Lossless Predictive Audio Compression (LPAC)
				'lpac' => array(
							'pattern'   => '^LPAC',
							'group'     => 'audio',
							'module'    => 'lpac',
							'mime_type' => 'application/octet-stream',
						),

				// MIDI - audio       - MIDI (Musical Instrument Digital Interface)
				'midi' => array(
							'pattern'   => '^MThd',
							'group'     => 'audio',
							'module'    => 'midi',
							'mime_type' => 'audio/midi',
						),

				// MAC  - audio       - Monkey's Audio Compressor
				'mac'  => array(
							'pattern'   => '^MAC ',
							'group'     => 'audio',
							'module'    => 'monkey',
							'mime_type' => 'audio/x-monkeys-audio',
						),

// has been known to produce false matches in random files (e.g. JPEGs), leave out until more precise matching available
//				// MOD  - audio       - MODule (assorted sub-formats)
//				'mod'  => array(
//							'pattern'   => '^.{1080}(M\\.K\\.|M!K!|FLT4|FLT8|[5-9]CHN|[1-3][0-9]CH)',
//							'group'     => 'audio',
//							'module'    => 'mod',
//							'option'    => 'mod',
//							'mime_type' => 'audio/mod',
//						),

				// MOD  - audio       - MODule (Impulse Tracker)
				'it'   => array(
							'pattern'   => '^IMPM',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 'it',
							'mime_type' => 'audio/it',
						),

				// MOD  - audio       - MODule (eXtended Module, various sub-formats)
				'xm'   => array(
							'pattern'   => '^Extended Module',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 'xm',
							'mime_type' => 'audio/xm',
						),

				// MOD  - audio       - MODule (ScreamTracker)
				's3m'  => array(
							'pattern'   => '^.{44}SCRM',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 's3m',
							'mime_type' => 'audio/s3m',
						),

				// MPC  - audio       - Musepack / MPEGplus
				'mpc'  => array(
							'pattern'   => '^(MPCK|MP\\+|[\\x00\\x01\\x10\\x11\\x40\\x41\\x50\\x51\\x80\\x81\\x90\\x91\\xC0\\xC1\\xD0\\xD1][\\x20-\\x37][\\x00\\x20\\x40\\x60\\x80\\xA0\\xC0\\xE0])',
							'group'     => 'audio',
							'module'    => 'mpc',
							'mime_type' => 'audio/x-musepack',
						),

				// MP3  - audio       - MPEG-audio Layer 3 (very similar to AAC-ADTS)
				'mp3'  => array(
							'pattern'   => '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]',
							'group'     => 'audio',
							'module'    => 'mp3',
							'mime_type' => 'audio/mpeg',
						),

				// OFR  - audio       - OptimFROG
				'ofr'  => array(
							'pattern'   => '^(\\*RIFF|OFR)',
							'group'     => 'audio',
							'module'    => 'optimfrog',
							'mime_type' => 'application/octet-stream',
						),

				// RKAU - audio       - RKive AUdio compressor
				'rkau' => array(
							'pattern'   => '^RKA',
							'group'     => 'audio',
							'module'    => 'rkau',
							'mime_type' => 'application/octet-stream',
						),

				// SHN  - audio       - Shorten
				'shn'  => array(
							'pattern'   => '^ajkg',
							'group'     => 'audio',
							'module'    => 'shorten',
							'mime_type' => 'audio/xmms-shn',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// TAK  - audio       - Tom's lossless Audio Kompressor
				'tak'  => array(
							'pattern'   => '^tBaK',
							'group'     => 'audio',
							'module'    => 'tak',
							'mime_type' => 'application/octet-stream',
						),

				// TTA  - audio       - TTA Lossless Audio Compressor (http://tta.corecodec.org)
				'tta'  => array(
							'pattern'   => '^TTA',  // could also be '^TTA(\\x01|\\x02|\\x03|2|1)'
							'group'     => 'audio',
							'module'    => 'tta',
							'mime_type' => 'application/octet-stream',
						),

				// VOC  - audio       - Creative Voice (VOC)
				'voc'  => array(
							'pattern'   => '^Creative Voice File',
							'group'     => 'audio',
							'module'    => 'voc',
							'mime_type' => 'audio/voc',
						),

				// VQF  - audio       - transform-domain weighted interleave Vector Quantization Format (VQF)
				'vqf'  => array(
							'pattern'   => '^TWIN',
							'group'     => 'audio',
							'module'    => 'vqf',
							'mime_type' => 'application/octet-stream',
						),

				// WV  - audio        - WavPack (v4.0+)
				'wv'   => array(
							'pattern'   => '^wvpk',
							'group'     => 'audio',
							'module'    => 'wavpack',
							'mime_type' => 'application/octet-stream',
						),


				// Audio-Video formats

				// ASF  - audio/video - Advanced Streaming Format, Windows Media Video, Windows Media Audio
				'asf'  => array(
							'pattern'   => '^\\x30\\x26\\xB2\\x75\\x8E\\x66\\xCF\\x11\\xA6\\xD9\\x00\\xAA\\x00\\x62\\xCE\\x6C',
							'group'     => 'audio-video',
							'module'    => 'asf',
							'mime_type' => 'video/x-ms-asf',
							'iconv_req' => false,
						),

				// BINK - audio/video - Bink / Smacker
				'bink' => array(
							'pattern'   => '^(BIK|SMK)',
							'group'     => 'audio-video',
							'module'    => 'bink',
							'mime_type' => 'application/octet-stream',
						),

				// FLV  - audio/video - FLash Video
				'flv' => array(
							'pattern'   => '^FLV[\\x01]',
							'group'     => 'audio-video',
							'module'    => 'flv',
							'mime_type' => 'video/x-flv',
						),

				// IVF - audio/video - IVF
				'ivf' => array(
							'pattern'   => '^DKIF',
							'group'     => 'audio-video',
							'module'    => 'ivf',
							'mime_type' => 'video/x-ivf',
						),

				// MKAV - audio/video - Mastroka
				'matroska' => array(
							'pattern'   => '^\\x1A\\x45\\xDF\\xA3',
							'group'     => 'audio-video',
							'module'    => 'matroska',
							'mime_type' => 'video/x-matroska', // may also be audio/x-matroska
						),

				// MPEG - audio/video - MPEG (Moving Pictures Experts Group)
				'mpeg' => array(
							'pattern'   => '^\\x00\\x00\\x01[\\xB3\\xBA]',
							'group'     => 'audio-video',
							'module'    => 'mpeg',
							'mime_type' => 'video/mpeg',
						),

				// NSV  - audio/video - Nullsoft Streaming Video (NSV)
				'nsv'  => array(
							'pattern'   => '^NSV[sf]',
							'group'     => 'audio-video',
							'module'    => 'nsv',
							'mime_type' => 'application/octet-stream',
						),

				// Ogg  - audio/video - Ogg (Ogg-Vorbis, Ogg-FLAC, Speex, Ogg-Theora(*), Ogg-Tarkin(*))
				'ogg'  => array(
							'pattern'   => '^OggS',
							'group'     => 'audio',
							'module'    => 'ogg',
							'mime_type' => 'application/ogg',
							'fail_id3'  => 'WARNING',
							'fail_ape'  => 'WARNING',
						),

				// QT   - audio/video - Quicktime
				'quicktime' => array(
							'pattern'   => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)',
							'group'     => 'audio-video',
							'module'    => 'quicktime',
							'mime_type' => 'video/quicktime',
						),

				// RIFF - audio/video - Resource Interchange File Format (RIFF) / WAV / AVI / CD-audio / SDSS = renamed variant used by SmartSound QuickTracks (www.smartsound.com) / FORM = Audio Interchange File Format (AIFF)
				'riff' => array(
							'pattern'   => '^(RIFF|SDSS|FORM)',
							'group'     => 'audio-video',
							'module'    => 'riff',
							'mime_type' => 'audio/wav',
							'fail_ape'  => 'WARNING',
						),

				// Real - audio/video - RealAudio, RealVideo
				'real' => array(
							'pattern'   => '^\\.(RMF|ra)',
							'group'     => 'audio-video',
							'module'    => 'real',
							'mime_type' => 'audio/x-realaudio',
						),

				// SWF - audio/video - ShockWave Flash
				'swf' => array(
							'pattern'   => '^(F|C)WS',
							'group'     => 'audio-video',
							'module'    => 'swf',
							'mime_type' => 'application/x-shockwave-flash',
						),

				// TS - audio/video - MPEG-2 Transport Stream
				'ts' => array(
							'pattern'   => '^(\\x47.{187}){10,}', // packets are 188 bytes long and start with 0x47 "G".  Check for at least 10 packets matching this pattern
							'group'     => 'audio-video',
							'module'    => 'ts',
							'mime_type' => 'video/MP2T',
						),

				// WTV - audio/video - Windows Recorded TV Show
				'wtv' => array(
							'pattern'   => '^\\xB7\\xD8\\x00\\x20\\x37\\x49\\xDA\\x11\\xA6\\x4E\\x00\\x07\\xE9\\x5E\\xAD\\x8D',
							'group'     => 'audio-video',
							'module'    => 'wtv',
							'mime_type' => 'video/x-ms-wtv',
						),


				// Still-Image formats

				// BMP  - still image - Bitmap (Windows, OS/2; uncompressed, RLE8, RLE4)
				'bmp'  => array(
							'pattern'   => '^BM',
							'group'     => 'graphic',
							'module'    => 'bmp',
							'mime_type' => 'image/bmp',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// GIF  - still image - Graphics Interchange Format
				'gif'  => array(
							'pattern'   => '^GIF',
							'group'     => 'graphic',
							'module'    => 'gif',
							'mime_type' => 'image/gif',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// JPEG - still image - Joint Photographic Experts Group (JPEG)
				'jpg'  => array(
							'pattern'   => '^\\xFF\\xD8\\xFF',
							'group'     => 'graphic',
							'module'    => 'jpg',
							'mime_type' => 'image/jpeg',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// PCD  - still image - Kodak Photo CD
				'pcd'  => array(
							'pattern'   => '^.{2048}PCD_IPI\\x00',
							'group'     => 'graphic',
							'module'    => 'pcd',
							'mime_type' => 'image/x-photo-cd',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// PNG  - still image - Portable Network Graphics (PNG)
				'png'  => array(
							'pattern'   => '^\\x89\\x50\\x4E\\x47\\x0D\\x0A\\x1A\\x0A',
							'group'     => 'graphic',
							'module'    => 'png',
							'mime_type' => 'image/png',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// SVG  - still image - Scalable Vector Graphics (SVG)
				'svg'  => array(
							'pattern'   => '(<!DOCTYPE svg PUBLIC |xmlns="http://www\\.w3\\.org/2000/svg")',
							'group'     => 'graphic',
							'module'    => 'svg',
							'mime_type' => 'image/svg+xml',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// TIFF - still image - Tagged Information File Format (TIFF)
				'tiff' => array(
							'pattern'   => '^(II\\x2A\\x00|MM\\x00\\x2A)',
							'group'     => 'graphic',
							'module'    => 'tiff',
							'mime_type' => 'image/tiff',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// EFAX - still image - eFax (TIFF derivative)
				'efax'  => array(
							'pattern'   => '^\\xDC\\xFE',
							'group'     => 'graphic',
							'module'    => 'efax',
							'mime_type' => 'image/efax',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// Data formats

				// ISO  - data        - International Standards Organization (ISO) CD-ROM Image
				'iso'  => array(
							'pattern'   => '^.{32769}CD001',
							'group'     => 'misc',
							'module'    => 'iso',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
							'iconv_req' => false,
						),

				// HPK  - data        - HPK compressed data
				'hpk'  => array(
							'pattern'   => '^BPUL',
							'group'     => 'archive',
							'module'    => 'hpk',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// RAR  - data        - RAR compressed data
				'rar'  => array(
							'pattern'   => '^Rar\\!',
							'group'     => 'archive',
							'module'    => 'rar',
							'mime_type' => 'application/vnd.rar',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// SZIP - audio/data  - SZIP compressed data
				'szip' => array(
							'pattern'   => '^SZ\\x0A\\x04',
							'group'     => 'archive',
							'module'    => 'szip',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// TAR  - data        - TAR compressed data
				'tar'  => array(
							'pattern'   => '^.{100}[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20\\x00]{12}[0-9\\x20\\x00]{12}',
							'group'     => 'archive',
							'module'    => 'tar',
							'mime_type' => 'application/x-tar',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// GZIP  - data        - GZIP compressed data
				'gz'  => array(
							'pattern'   => '^\\x1F\\x8B\\x08',
							'group'     => 'archive',
							'module'    => 'gzip',
							'mime_type' => 'application/gzip',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// ZIP  - data         - ZIP compressed data
				'zip'  => array(
							'pattern'   => '^PK\\x03\\x04',
							'group'     => 'archive',
							'module'    => 'zip',
							'mime_type' => 'application/zip',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// XZ   - data         - XZ compressed data
				'xz'  => array(
							'pattern'   => '^\\xFD7zXZ\\x00',
							'group'     => 'archive',
							'module'    => 'xz',
							'mime_type' => 'application/x-xz',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// Misc other formats

				// PAR2 - data        - Parity Volume Set Specification 2.0
				'par2' => array (
							'pattern'   => '^PAR2\\x00PKT',
							'group'     => 'misc',
							'module'    => 'par2',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// PDF  - data        - Portable Document Format
				'pdf'  => array(
							'pattern'   => '^\\x25PDF',
							'group'     => 'misc',
							'module'    => 'pdf',
							'mime_type' => 'application/pdf',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// MSOFFICE  - data   - ZIP compressed data
				'msoffice' => array(
							'pattern'   => '^\\xD0\\xCF\\x11\\xE0\\xA1\\xB1\\x1A\\xE1', // D0CF11E == DOCFILE == Microsoft Office Document
							'group'     => 'misc',
							'module'    => 'msoffice',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// TORRENT             - .torrent
				'torrent' => array(
							'pattern'   => '^(d8\\:announce|d7\\:comment)',
							'group'     => 'misc',
							'module'    => 'torrent',
							'mime_type' => 'application/x-bittorrent',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				 // CUE  - data       - CUEsheet (index to single-file disc images)
				 'cue' => array(
							'pattern'   => '', // empty pattern means cannot be automatically detected, will fall through all other formats and match based on filename and very basic file contents
							'group'     => 'misc',
							'module'    => 'cue',
							'mime_type' => 'application/octet-stream',
						   ),

			);
		}

		return $format_info;
	}
   
}
