## ImageDataDupes
A simple **cross-platform** (Windows, Linux, MacOS and many other) PHP script to find dupilicates of  photo exact duplicates. Unlike most tools, this one will compare just image (by MD5 hash - compromise between speed and collisions statistically once per 10^64 files), so **changing metadata, like adding title or GPS data, still counts as duplicate**. It bases on **Exiftool** raw image hash, which hashes only image data, not whole file. It also means it uses rich reading abilities of the **Exiftool**, which means [enormous list of supported file types](https://exiftool.org/#supported), inlcuding all standard lossy files, **raw camera files**. It just reads file, deletion is up to you, **no changes are made by script**. 

### Output
The tool will group files into duplicate groups (first file being "parent") and it also informs you that:
* file is identical (hash comparison)
* file is identical in size, but contents differs
* file has different size (it gives size delta in bytes)
* file is a file system link (it says if it's hard or symbolic link)

Also, to make comparison more efficient, it shows flags next to each file in format of `T-1` where 1 is count of metadata of `T` type:
* E (yellow) - file has Exif
* G (green) - GPS (take note that even without real GPS data, many cameras incorporate single GPS entry, so number would be a proper way to recognize if it's really geotagged)
* X (red) - XMP
* I (cyan) - IPTC

#### Example output
##### Text form
```
[INFO] Using cache file digest.txt
[INFO] Getting data done! Found 41 files, checking for duplicates
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

Duplicates of: D:\_FOTO\foo.CR2 E-66 G-1 X-1
 * D:\_FOTO\IMG_1245 - Copy.CR2 E-66 G-1 X-1 [identical]
 * D:\_FOTO\IMG_1245.CR2 E-66 G-1 X-1 I-2 [hard link]
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

Duplicates of: D:\_FOTO\IMG_1253.CR2 E-66 G-1 X-1
 * D:\_FOTO\IMG_1254.CR2 E-66 G-1 X-1 [identical]
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
```

##### Screenshot, because colorz and stuff

![example](https://github.com/user-attachments/assets/53d05729-1b0a-47af-a908-b38f728fad9a)


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
* `cacheFile` - name of the file with cached hashes, it's unlikely you need to change it

### Installation/executing:
1) [optional] review settings in digest.php (between `CONFIG START` and `CONFIG END`)
2) run the tool (`php digest.php`)
3) it will start checking current directory or one you provided as parameter, like `php digest.php c:\pathtophotos`
4) wait for initial scan
5) after it's complete, you may review files. Next time full scan won't happen, so you may delete files and then just run it again to see difference
6) changing directory will force the tool to rescan. 


### Version history
#### 0.0.2 "Volucella zonaria"
* files now have flags, showing type of metadata it contains (in format `T-1`, where `T` is type and `1` is count of fields). See output section for more details.
* ignoring file system links removed, it caused more problems than gave gains
* instead tool will now notify you if file is link and what type (hard or symbolic)
* changed color for identical files (now they are more visible - black on red, instead red on black)
* this tool now checks for hard links as well
* include information about type of link, if ignoring links is on
* include version.txt in release for automated checkings of updates


#### 0.0.1 "Callopistromyia annulipes" 
* initial version, no changes

### Known bugs
* when cache file is present and tool will be run with different directory parameter, it will read cache, even if it's for different files
* file will be marked as link if it's hardlinked to file outside search scope

### To do
* I'd love to have it command line parameter driven, so no more changing config in PHP
* Some day rewrite to Python, so it could be compiled and released as binary
* use sqlite for caching
* run exiftool asynchronously, so user can see progress
