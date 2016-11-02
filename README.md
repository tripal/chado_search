# Mainab Chado Search
Mainlab Chado Search is a module that enables advanced search function for biological 
data stored in a Tripal/Chado database (see http://gmod.org/wiki/Chado and 
http://tripal.info). By default,  a set of search interfaces are provided, such as 'Gene Search' 
for searching genes and/or transcripts, 'Marker Search' for searching genetic markers, and 
'Sequence Search' for searching any sequences stored in the Chado feature table. Searches 
for other data types, such as QTL, Map, Trait, Stock, Organism are also provided but may 
require modification to the materialized view to adjust for site-specific data storage.

The Mainlab Chado Search module depends on the Tripal materialized views system for 
gathering data the site administrator wishes to make searchable. Using a materialized view 
not only improves the search performance, but also allows the administrator to restrict 
data by modifying the materialized view definition. This design also allows site developers 
to adopt this module when data in Chado is not stored in the exact same way
(See ‘Customization’ section). Data collecton templates and loader (Mainlab Chado Loader, 
see https://www.github.com/mainlab-dev/mainlab_chado_loader) are also available as a 
separate module.

The Mainlab Chado Search is created by Main Bioinformatics Lab (Main Lab) at 
Washington State University. Information about the Main Lab can be found at: 
https://www.bioinfo.wsu.edu
 
## Requirement
 - Drupal 7.x
 - Tripal 7.x-2.x

## Version
1.0.0

## Download
The Mainlab Chado Search module can be downloaded from GitHub:

https://www.github.com/mainlab-dev/chado_search

## Installation
After downloading the module, extract it into your site's module directory 
(e.g. sites/all/modules) then follow the instructions below:

1. Create a 'settings.conf' file in the 'chado_search/file' directory. For example,

    ```
    cd chado_search/file
    cp default.settings.txt settings.conf
    ```
    
    Note1: if you make changes to the 'settings.conf' after the module is enabled, you'll need 
    to run the following drush command to make it effective:
    
    ```
    drush csreload
    ```
    
    Note2: Mainlab Chado Search currently provides example setting files listed below. You 
    can find more information about these setting files in the 'Example Setting Files' section 
    in this document.
      - default.settings.txt
      - cottongen.settings.txt
      - gdr.settings.txt
      - legume.settings.txt

2. Enable the module by using the Drupal administrative interface:
 
      Go to: Modules, check Mainlab Chado Search (under the Mainlab category) and save
       
    or by using the 'drush' command:
    
    ```
    drush pm-enable chado_search
    ```
    
    This will create all search interfaces listed in the 'settings.conf' and all materialized views 
    required for the search to function.
    
3. Populate the materialized views by using the Tripal administrative interface:
  1. Go to: Tripal > Chado Schema > Materialized Views
  2. Identify corresponding materialized views in your 'settings.conf' and click on 
        'Populate' to submit a Tripal job.
  3. Launch the job from the console. This can usually be done by switching to the web
        root directory and issue the drush command:
        
        ```
        drush trp-run-jobs --username=<an admin user>
        ```
        
  Note: More information about using the Tripal Materialized Views system can be found at 
           http://tripal.info/node/105)

4. Visit the search page in your browse by going to the path set in your 'settings.conf' file.
    For example, the default 'Sequence Search' can be accessed by visiting:
    http://your.site/find/features
    
    Alternatively,  you can visit http://your.site/admin/mainlab/chado_search/settings to get 
    a full list of searches on your site.
    
    Note: you can change the path to anything you like but remember to clear the Drupal 
    cache to refresh Drupal's menu registry. An easy way to do so is to run the following 
    drush command after you make the change:
      drush csreload

## Administration
 - Enabling/Disabling a search:
 
   Go to: Mainlab > Chado Search and click on either 'Disable' or 'Enable' for a search 
   i.e. http://your.site/admin/mainlab/chado_search/settings
   
   Note: Make sure your web server has write permission to the settings.conf so you can
              turn a search on or off using the web interface.
                 
 - Adding/Deleting data for a search:
 
   After adding or deleting data to/from Chado, you'll need to update related materialized 
   views to reflect the change. Information about using the Tripal Materialized Views system 
   can be found at http://tripal.info/node/105

 - Maintaining the materialized views:
 
    You can make changes to the materialized views using the Tripal MView interface. You  
    can identify the materialized views created by Mainlab Chado Search by looking for the 
    prefix 'chado_search_'. If you make changes to a default materialized view that comes 
    with Mainlab Chado Search and later decide to get it back, delete the materialized 
    view by using the Tripal administrative interface and refresh the 'settings.conf' using 
    the following drush command.
      drush csreload
      
    Note: you'll still need to populate the materialized view using Tripal's administrative 
    interface after bringing it back.
    
## Customization
You can customize the search for your site by modifying the 'settings.conf' file and using 
the Tripal MView interface. 

For developers, you can also create your own search by copying/modifying, or creating 
the search interface php and/or the materialized view definition php files. See details below 
in the Create New Search section.

## Create New Search (for Developers)
For site developers, Mainlab Chado Search provides a set of APIs that'll be useful for 
developing new search interfaces. At this time, no technical support is offered, nor is the 
detailed API documentation. An example (i.e. Node Search) below, however, shows 
minimal steps required to create a new search from scratch:

1. Create a stanza in the 'settings.conf'. For example, a basic stanza looks like:
  ```
  [Node Search]
  id=node_search
  path=find/node
  file=includes/search/example/node_search.php
  enabled=1
  ```
  - Note1: See 'default.settings.txt' for additional information about the configurable options.
  - Note2: Use of materialized view is recommended but not required.

2. Create the search interface php file 'includes/search/example/node_search.php' with the
following content:
  ```
  <?php
  
  use ChadoSearch\Set;
  use ChadoSearch\Sql;
  
  /*************************************************************
   * hook_form()  
   */
  function chado_search_node_search_form ($form) {
    $form->addTextFilter(
        Set::textFilter()
        ->id('title')
        ->title('Title')
    );
    $form->addSubmit();    
    return $form;
  }
  
  /*************************************************************
   * hook_form_submit()
   */
   function chado_search_node_search_form_submit ($form, &$form_state) {
    $sql = "SELECT nid, title FROM node";
    $where [0] = Sql::textFilter('title', $form_state, 'title');
    Set::result()
    ->sql($sql)
    ->where($where)
    ->execute($form, $form_state);
  }
  ```

3. Make the 'settings.conf' effective:
  ```
  drush csreload 
  (or 'drush cc menu' at minimum)
  ```
4. Access the search page by visiting: http://your.site/find/node

## Example Setting Files
  1. default.settings.txt
     this file contains three search interfaces: 
    * Sequence Search
    * Marker Search
    * Gene Search

  2. cottongen.settings.txt
      this file contains the following search interfaces (with URL as live example): 
    * [Sequence Search]
        (https://www.cottongen.org/find/features)
        
    * [Search for Mapped Markers]
        (https://www.cottongen.org/find/mapped_markers)
        
    * [Advanced Marker Search]
        (https://www.cottongen.org/find/markers)
        
    * [Marker Source Information]
        (https://www.cottongen.org/find/marker/source)
        
    * [Search Mapped Sequence by Map Name]
        (https://www.cottongen.org/find/mapped_sequence/map)
        
    * [Search Mapped Sequence by Chromosome Number]
        (https://www.cottongen.org/find/mapped_sequence/chromosome)
        
    * [Search Mapped Sequence by Genome Group]
        (https://www.cottongen.org/find/mapped_sequence/genome)
        
    * [Search Markers on Nearby Marker Loci]
        (https://www.cottongen.org/find/nearby_markers)
        
    * [Search Markers on Nearby QTL]
        (https://www.cottongen.org/find/qtl_nearby_markers)
        
    * [Trait Evaluation Search (Qualitative Traits)]
        (https://www.cottongen.org/find/qualitative_traits)
        
    * [Trait Evaluation Search (Quantitative Traits)]
        (https://www.cottongen.org/find/quantitative_traits)
        
    * [Germplasm Search]
        (https://www.cottongen.org/find/germplasm)
        
    * [Germplasm Search (by Pedigree)]
        (https://www.cottongen.org/find/germplasm/pedigree)
        
    * [Germplasm Search (by Country)]
        (https://www.cottongen.org/find/germplasm/country)
        
    * [Germplasm Search (by Collection)]
        (https://www.cottongen.org/find/germplasm/collection)
        
    * [Germplasm Search (by Image)]
        (https://www.cottongen.org/find/germplasm/image)
        
    * [Gene Search]
        (https://www.cottongen.org/find/genes)
        
    * [QTL Search]
        (https://www.cottongen.org/find/qtl)
        
    * [ND Geolocation]
        (https://www.cottongen.org/find/nd_geolocation)
        
    * [Map Data Summary]
        (https://www.cottongen.org/find/featuremap)
        
    * [Species Summary]
        (https://www.cottongen.org/find/species)

  3. gdr.settings.txt
      this file contains the following search interfaces (with URL as live example): 
    * [Gene Search]
        (https://www.rosaceae.org/search/genes)
        
    * [Sequence Search]
        (https://www.rosaceae.org/search/features)
        
    * [Marker Search]
        (https://www.rosaceae.org/search/markers)
        
    * [Search Markers on Nearby Loci]
        (https://www.rosaceae.org/search/nearby_markers)
        
    * [Germplasm Search]
        (https://www.rosaceae.org/search/germplasm)
        
    * [Germplasm Image Search]
        (https://www.rosaceae.org/search/germplasm/image)
        
    * [Haplotype Block Search]
        (https://www.rosaceae.org/search/haplotype_blocks)
        
    * [QTL Search]
        (https://www.rosaceae.org/search/qtl)
        
    * [Search Maps]
        (https://www.rosaceae.org/search/featuremap)
        
    * [Species Summary]
        (https://www.rosaceae.org/search/species)
        
    * [SSR Genotype Search]
        (https://www.rosaceae.org/search/ssr_genotype)
        
    * [SNP Genotype Search]
        (https://www.rosaceae.org/search/snp_genotype)
        
  4. legume.settings.txt
      this file contains the following search interfaces (with URL as live example): 
      
    * [Sequence Search]
        (https://www.coolseasonfoodlegume.org/find/features)
        
    * [Marker Search]
        (https://www.coolseasonfoodlegume.org/find/markers)
        
    * [Search Markers on Nearby Loci]
        (https://www.coolseasonfoodlegume.org/find/nearby_markers)
        
    * [Transcript Search]
        (https://www.coolseasonfoodlegume.org/find/transcript)
        
    * [Germplasm Search]
        (https://www.coolseasonfoodlegume.org/find/germplasms)
        
    * [QTL Search]
        (https://www.coolseasonfoodlegume.org/find/qtl)
        
## Problems/Suggestions
Mainlab Chado Search module is still under active development. For questions or bug 
report, please contact the developers at the Main Bioinformatics Lab by emailing to: 
dev@bioinfo.wsu.edu