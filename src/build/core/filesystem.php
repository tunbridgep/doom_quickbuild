<?php

#the base class for which our filesystem objecs are defined
abstract class FilesystemObject
{
	protected $path;

	#this makes sure we always have the right directory separators,
	#in case someone (usually me) accidentally specifies them in the wrong
	#direction (forward or back slashes), which should help people
	#who are transitioning a build script from Linux to Windows, and vice
	#versa
	protected function fix_path()
	{
		$path = $this->path;

		#fix paths in the wrong direction (wrong OS)
		$path = str_replace("\\",DIRECTORY_SEPARATOR,$path);
		$path = str_replace("/",DIRECTORY_SEPARATOR,$path);

		#fix doubled-up slashes
		while(strpos($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false)
			$path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$path);

		#PHP is silly and file_exists doesn't work properly when we have paths starting with "~/"
		#so we need to fix it
		if (strpos($path,"~/") == 0)
		{
			$home = $_SERVER['HOME'].DIRECTORY_SEPARATOR;
			$path = str_replace("~/",$home,$path);
		}

		$this->path = $path;
	}

	public function GetPath()
	{
		return $this->path;
	}

	public function GetAbsolutePath()
	{
		if (!file_exists($this->path))
			die("Requested an absolute path for a non-existent file"); #this is required because the realpath function is useless and badly implemented
		if (is_dir($this->path))
			return realpath($this->path).DIRECTORY_SEPARATOR;
		else
			return realpath($this->path);
	}

	#get the base name for our file (just the filename),
	#optionally get it without an extension
	public function GetName(bool $extension = true)
	{
		$base = basename($this->path);
		if ($extension)
			return $base;
		else
			return substr($base, 0, -(strlen($this->GetExtension())));
	}

	#get just the extension for our file
	public function GetExtension()
	{
		return ".".pathinfo($this->path, PATHINFO_EXTENSION);
	}

	public function Rename(string $new)
	{
		rename($this->GetPath(),$new);
	}

	public abstract function Exists();
	public abstract function Delete();
	public abstract function CopyTo(Folder $dest);
}

class File extends FilesystemObject
{
	public function __construct(string $path)
	{
		$this->path = $path;
		$this->fix_path();
	}

	public function Exists()
	{
		return file_exists($this->path);
	}

	public function Write(string $content, bool $append = false)
	{
		if (is_dir($this->path))
		{
			die("attempting to write file contents to a directory");
		}
		else if ($append && $this->Exists())
		{
			file_put_contents($this->path,PHP_EOL.$content,FILE_APPEND);
		}
		else
		{
			file_put_contents($this->path,$content);
		}
	}

	public function CopyTo(Folder $dest)
	{
		copy($this->path,$dest->GetPath().$this->GetName());
	}

	public function Delete()
	{
		if ($this->Exists())
			unlink($this->path);
	}
}

class Folder extends FilesystemObject
{
	public function __construct(string $path)
	{
		$this->path = $path;
		$this->fix_path();
		$this->path = rtrim($this->path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR; #make sure we ALWAYS have a directory separator at the end of our path
	}

	public function Exists()
	{
		return is_dir($this->path);
	}

	public function Create(bool $recreate = false)
	{
		if ($recreate && file_exists($this->path))
		{
			Folder::recursive_delete($this->path);
		}
		@mkdir($this->path);
	}

	public function Delete()
	{
		if ($this->Exists())
			Folder::recursive_delete($this->path);
	}

	#returns an array containing all children and subchildren etc
	#optionally we may specify a pattern to filter our contents by
	public function GetContents(string $pattern = "*")
	{
		if ($this->Exists())
			return Folder::rglob($this->path.$pattern);
		else
			return null;
	}

	public function GetSubPath(string $name)
	{
		if (substr($name,-1) == DIRECTORY_SEPARATOR)
			$sub = new Folder($this->GetPath().DIRECTORY_SEPARATOR.$name);
		else
			$sub = new File($this->GetPath().DIRECTORY_SEPARATOR.$name);
		return $sub;
	}

	public function CopyTo(Folder $dest)
	{
		Folder::recursive_copy($this->path,$dest->GetPath());
	}

	#this recursively globs any folder, making a big array containing all it's children and their children etc
	private static function rglob($pattern, $flags = 0)
	{
		$files = array();
		foreach (glob($pattern, $flags) as $file)
			if (!is_dir($file))
				array_push($files,$file);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
			$files = array_merge($files, Folder::rglob($dir.'/'.basename($pattern), $flags));
		return $files;
	}

	#taken from https://www.php.net/manual/en/function.rmdir.php
	#written by nbari at dalmp dot com and heavily adapted
	private static function recursive_delete($dir)
	{
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file)
		{
			is_dir("$dir/$file") ? Folder::recursive_delete("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	#recursive copy function, which will copy everything from src to dest (and all children)
	private static function recursive_copy(string $src,string $dst)
	{ 
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) )
		{ 
			if (( $file != '.' ) && ( $file != '..' ))
			{ 
				if ( is_dir($src . '/' . $file) )
					Folder::recursive_copy($src . '/' . $file,$dst . '/' . $file); 
				else
					copy($src . '/' . $file,$dst . '/' . $file); 
			} 
		} 
		closedir($dir); 
	} 

}
?>
