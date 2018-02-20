<?php 

// Link to node by nid. If nid is unavailable return NULL
function chado_search_link_node ($nid) {
    if ($nid) {
        return "/node/$nid";
    } else {
        return NULL;
    }
}

function chado_search_link_entity ($base_table, $record_id) {
    $link = NULL;
    // tripal v2 link (node)
    $nid = chado_get_nid_from_id ($base_table, $record_id);
    if ($nid) {
        $link = "/node/$nid";
    }
    // tripal v3 link (entity)
    if (function_exists('chado_get_record_entity_by_table') && $record_id) {
        $entity_id = chado_get_record_entity_by_table ($base_table, $record_id);
        if ($entity_id) {
            $link = "/bio_data/$entity_id";
        }
    }
    return $link;
}

// Link to node by nid. If nid is unavailable return NULL
function chado_search_link_url ($url) {
    if ($url) {
        return $url;
    } else {
        return NULL;
    }
}

// Link feature
function chado_search_link_feature ($feature_id) {
    return chado_search_link_entity('feature', $feature_id);
}

// Link genetic_marker feature
function chado_search_link_genetic_marker ($locus_feature_id) {
    // Convert the feature_id of marker_locus to genetic_marker
    $fid = chado_query("SELECT object_id FROM {feature_relationship} FR WHERE subject_id = $locus_feature_id AND type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'instance_of' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'relationship'))")->fetchField();
    return chado_search_link_entity('feature', $fid);
}

// Link organism
function chado_search_link_organism ($organism_id) {
    return chado_search_link_entity('organism', $organism_id);
}

// Link featuremap
function chado_search_link_featuremap ($featuremap_id) {
    return chado_search_link_entity('featuremap', $featuremap_id);
}

// Link library
function chado_search_link_library ($library_id) {
    return chado_search_link_entity('library', $library_id);
}

// Link analysis
function chado_search_link_analysis ($analysis_id) {
    return chado_search_link_entity('analysis', $analysis_id);
}

// Link project
function chado_search_link_project ($project_id) {
    return chado_search_link_entity('project', $project_id);
}

// Link pub
function chado_search_link_pub ($pub_id) {
    return chado_search_link_entity('pub', $pub_id);
}

// Link stock
function chado_search_link_stock ($stock_id) {
    return chado_search_link_entity('stock', $stock_id);
}

// Link contact
function chado_search_link_contact ($contact_id) {
    return chado_search_link_entity('contact', $contact_id);
}

// Link nd_geolocation
function chado_search_link_nd_geolocation ($nd_geolocation_id) {
    return chado_search_link_entity('nd_geolocation', $nd_geolocation_id);
}

// Link jbrowse
function chado_search_link_jbrowse ($paras) {
    $srcfeature_id = $paras [0];
    $loc = $paras[1];
    $sql =
    "SELECT value
    FROM {feature} F
    INNER JOIN {analysisfeature} AF ON F.feature_id = AF.feature_id
    INNER JOIN {analysis} A ON A.analysis_id = AF.analysis_id
    INNER JOIN {analysisprop} AP ON AP.analysis_id = A.analysis_id
    INNER JOIN {cvterm} V ON V.cvterm_id = AP.type_id
    WHERE
    V.name = 'JBrowse URL' AND
    F.feature_id = :srcfeature_id";
    $jbrowse = $srcfeature_id ? chado_query($sql, array('srcfeature_id' => $srcfeature_id))->fetchField() : NULL;
    if ($jbrowse) {
        return chado_search_link_url ($jbrowse . $loc);
    }
    else {
        return NULL;
    }
}

// Link GRIN
function chado_search_link_grin ($grin) {
    return 'http://www.ars-grin.gov/cgi-bin/npgs/html/taxon.pl?' . $grin;
}