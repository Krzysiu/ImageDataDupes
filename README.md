## ImageDataDupes
A simple **cross-platform** (Windows, Linux, MacOS and many other) PHP script to find dupilicates of  photo exact duplicates. Unlike most tools, this one will compare just image (by MD5 hash - compromise between speed and collisions statistically once per 10^64 files), so **changing metadata, like adding title or GPS data, still counts as duplicate**. It bases on **Exiftool** raw image hash, which hashes only image data, not whole file. It also means it uses rich reading abilities of the **Exiftool**, which means [enormous list of supported file types](https://exiftool.org/#supported), inlcuding all standard lossy files, **raw camera files**. It just reads file, deletion is up to you, **no changes are made by script**. 

### Where it rocks
* when speed/support of many image formats is needed
* when you often fiddle with metadata, so you got files that have it different 

### Where it socks
* when you need comparison of 1:1 image, but of different formats (original raw and DNG, TIFF with different compression type)
* when you need to automate deletion (script is so simple, you can do change it, tho; for Windows it's always safer to use recycle bin and for command line I recommend my light and fast [recycle.exe](https://github.com/Krzysiu/cmdwinutils))
* when files are slightly different (it doesn't do any perpetual checking. For that, I recommend [Czkawka](https://github.com/qarmin/czkawka).

### Requirements
* Exiftool in system path (check by entering `exiftool -ver` in command line) - *for Windows* I recommend [alternative, blazing-fast build](https://oliverbetz.de/pages/Artikel/ExifTool-for-Windows) 
* PHP 7.x or 8.x
* [optional] for color output, Windows 10 or most Linux distros
* [optional] for good Unicode vs. PHP vs. Exiftool support, I'd recommend turning on Unicode support in Windows (see [this tutorial](https://stackoverflow.com/questions/9514300/text-encoding-on-wscript-arguments/79405392#79405392))

### Config (optional)
All config is inside PHP file, every setting is described and it will work well without additional config, but if you wish to set it up, here are possible settings:
* `recursiveMode` - possible values:
     - `0` - non-recursive
     - `1` (default) - recursive, all sub-directories, **except** directories starting with dot (Exiftool's `-r`)
     - `2` - recursive, ALL sub-directories, **including** directories with trailing dot (Exiftool's `-r.`)
* `ext` (default: `cr2,jpg,jpeg`)- comma separated extension (wihtout dot, **case insensitive**) to look for (recommended to set it up to your collection types, will greatly improve time - remember that almost always comparison between formats will not be marked as duplicate)
     - to process every extension set it either to `*` or `false`
* `path` - kind of useless, unless for special needs. Without it, it will use directory from where it's executed or one provided as parameter.
* `slashMode` - slash style (Windows vs. rest). **Windows will recognize Linux style, with exception** of copy/paste path into cmd line to quickly run file (paste name and hit enter) - if there are POSIX slashes on path - then it fails, so conversion enchances user experience. Possible values:
     - `0`  (default) - auto (**recommended**) - recognizes OS and sets proper value
     - `1` - Linux style (`bar/foo`)
     - `2` - Windows (`bar\foo`)
* `ignoreSymbolicLinks` - if true, it ommits files that are symbolic link (hard needs testing, but seems like they doesn't count and there's no support so far for Windows `.lnk` files)
* `cacheFile` - name of the file with cached hashes, it's unlikely you need to change it

### Installation/executing:
1) [optional] review settings in digest.php (between `CONFIG START` and `CONFIG END`)
2) run the tool (`php digest.php`)
3) it will start checking current directory or one you provided as parameter, like `php digest.php c:\pathtophotos`
4) wait for initial scan
5) after it's complete, you may review files. Next time full scan won't happen, so you may delete files and then just run it again to see difference
6) changing directory will force the tool to rescan. 

### Example output
#### Text form
```
D:\_FOTO\ImageDataDupes>php digest.php D:\_FOTO\20241115INDIANBUTTERFLYTEA
[INFO] Using cache file digest.txt
[INFO] Getting data done! Found 35 files, checking for duplicates
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

Duplicates of: D:\_FOTO\20241115INDIANBUTTERFLYTEA\IMG_8335.CR2
 * D:\_FOTO\20241115INDIANBUTTERFLYTEA\żółć.CR2 [identical]
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

Duplicates of: D:\_FOTO\20241115INDIANBUTTERFLYTEA\IMG_8345.CR2
 * D:\_FOTO\20241115INDIANBUTTERFLYTEA\IMG_8346 GPS.CR2 [different (+7148b)]
 * D:\_FOTO\20241115INDIANBUTTERFLYTEA\IMG_8346.CR2 [different, same size]
 * D:\_FOTO\20241115INDIANBUTTERFLYTEA\foobar\IMG_8346.CR2 [identical]
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


[INFO] 4 duplicates found in 2 groups
```

#### Screenshot, because colorz and stuff

![ex](https://github.com/user-attachments/assets/fa414d86-20aa-4b4b-ab10-53e3b2e7226b)


### Version history
#### 0.0.1 "Callopistromyia annulipes" 
* initial version, no changes

### Known bugs
* when cache file is present and tool will be run with different directory parameter, it will read cache, even if it's for different files

### To do
* I'd love to have it command line parameter driven, so no more changing config in PHP
* Some day rewrite to Python, so it could be compiled and released as binary
* Priority: give details of differences between files (i.e. show if file has XMP, IPTC, Exif or GPS block)
* ability to ignore Windows links (lnk) and hard links
