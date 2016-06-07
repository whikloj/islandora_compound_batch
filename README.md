# Islandora Compound Batch

## Introduction

This module extends the Islandora batch framework to provide a Drush command to ingest compound objects. Currently, only batches of compound objects that have a "flat" structure are supported. In other words, batches of compound objects whose children do not contain other children:

```
batch_directory/
├── compound_object_1
│   ├── child_1
│   ├── child_2
│   └── child_3
├── compound_object_2
│   ├── child_1
│   └── child_2
└── compound_object_3
    ├── child_1
    ├── child_2
    ├── child_3
    └── child_4
```

The ingest is a four-step process:

1. Arranging your objects in a directory structure like the one depicted above
2. Generating a structure file for each compound object
3. Batch preprocessing
4. Batch ingest

Step 2, generating structure files, is accomplished by running a standalone PHP script on the directory containing your objects. The last two are drush commands similar to those provided by other Islandora Batch modules. Details on each step are provided below.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/islandora/islandora)
* [Islandora Batch](https://github.com/Islandora/islandora_batch)

# Installation

Install as usual, see [this](https://drupal.org/documentation/install/modules-themes/modules-7) for further information.

## Configuration

There are no configuration options for this module.

### Usage

#### Step 1: Arranging your content and generating structure files

To prepare your compound objects, arrange them in a directory structure so that each parent object is in its own directory beneath the input directory, and within each parent object, each child object is in its own subdirectory. Each parent should contain a MODS.xml file, which is a sibling to the child object directories. Each child object directory should contain a MODS.xml file and must contain a file corresponding to the OBJ datastream. This file must be named OBJ and use an extension that will determine the content model to use for the child object. A sample input directory is:

```
input_directory
├── parent_one
│   ├── first_child
│   │   ├── MODS.xml
│   │   └── OBJ.jp2
│   ├── second_child
│   │   ├── MODS.xml
│   │   └── OBJ.jp2
│   └── MODS.xml
└── parent_two
    ├── first_child
    │   ├── MODS.xml
    │   └── OBJ.jp2
    ├── second_child
    │   ├── MODS.xml
    │   └── OBJ.jp2
    └── MODS.xml
```
The names of the parent and child directories don't matter, but the names of the files within them do, as explained below.

#### Step 2: Generating structure files

Once you have your content arranged, you will need to generate a 'structure file' for each object. To do this, run the `create_structure_files.php` script in this module's extras/scripts directory: `php create_strcutre_files.php path/to/directory/containing/compound_objects`. Running this script will add a `structure.xml` file to each parent object:

```
input_directory
├── parent_one
│   ├── first_child
│   │   ├── MODS.xml
│   │   └── OBJ.jp2
│   ├── second_child
│   │   ├── MODS.xml
│   │   └── OBJ.jp2
│   └── MODS.xml
│   └── structure.xml
└── parent_two
    ├── first_child
    │   ├── MODS.xml
    │   └── OBJ.jp2
    ├── second_child
    │   ├── MODS.xml
    │   └── OBJ.jp2
    └── MODS.xml
    └── structure.xml
```

If necessary, you can edit an object's `structure.xml` file to ensure that the children are in the order you want them to be in when they are ingested into the compound object in Islandora. The `structure.xml` files look like this:

```xml
<?xml version="1.0" encoding="utf-8"?>
<islandora_compound_object title="parent_one">
  <child content="first_child"/>
  <child content="second_child"/>
</islandora_compound_object>
```

The value of each `<child>` element is the name of the subdirectory containing the child object's MODS and OBJ files. The `title` attribute of the `<islandora_compound_object>` element is only used if the directory does not contain a MODS.xml file. Otherwise, the title assigned in the MODS file is used.  Each `structure.xml` file also contains a comment explaining how the file is interpreted by the Islandora Compound Batch module (the comment is omitted in this example for brevity).

#### Steps 3 and 4: Ingesting your prepared content into Islandora

After you have prepared your content, the remaining steps are much like those required by other Islandora Batch drush scripts.

The batch preprocessor is called as a drush script (see `drush help islandora_compound_batch_preprocess` for additional parameters):

`drush -v --user=admin islandora_compound_batch_preprocess --target=/path/to/input/directory --namespace=mynamespace --parent=mynamespace:collection`

This will populate the queue (stored in the Drupal database) with base entries.

The queue of preprocessed items is then processed by running the ingest command:

`drush -v --user=admin islandora_batch_ingest`

#### Pruning the list of relationships

This module records parent-child relationships in a database table. Periodically, you should prune this table by running the following command:

`drush --user=admin islandora_compound_batch_prune_relationships`

This command will remove relationships associated with Islandora batch sets that have been deleted. Relationships associated with batch sets that have not been deleted will remain in the database.

## OBJ extension to content model mappings

This module determines which content model to assign to child objects based on the extension of the child's OBJ file. The mapping used is:

```
jpeg => islandora:sp_basic_image
jpe => islandora:sp_basic_image
jpeg => islandora:sp_basic_image
gif => islandora:sp_basic_image
png => islandora:sp_basic_image
tif => islandora:sp_large_image_cmodel
tiff => islandora:sp_large_image_cmodel
jp2 => islandora:sp_large_image_cmodel
pdf => islandora:sp_pdf
mp3 => islandora:sp-audioCModel
mp4a => islandora:sp-audioCModel
m4a => islandora:sp-audioCModel
oga => islandora:sp-audioCModel
ogg => islandora:sp-audioCModel
flac => islandora:sp-audioCModel
wav => islandora:sp-audioCModel
mp4 => islandora:sp_videoCModel
m4v  => islandora:sp_videoCModel
mkv  => islandora:sp_videoCModel
mpeg => islandora:sp_videoCModel
mpe => islandora:sp_videoCModel
mpg => islandora:sp_videoCModel
qt => islandora:sp_videoCModel
mov => islandora:sp_videoCModel
ogv => islandora:sp_videoCModel
```

## Troubleshooting/Issues

Please open an issue in this Github repo's issue queue.

## Maintainers/Sponsors

* [Simon Fraser University Library](http://www.lib.sfu.ca/)

## To do

* Add support for hierarchical compound objects (i.e., with children that have children)
* Graphical user interface
* A way to allow overriding the extenstion to content model mapping

## Development

Pull requests are welcome, as are use cases and suggestions.

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
