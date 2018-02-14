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