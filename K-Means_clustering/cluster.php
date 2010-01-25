<?php
/**
 * K-means clustering with centroid and normalize value
 *
 * @see   http://phpir.com/clustering
 * @see   
 */

define(WITH_NORMALIZE, false);

$data = array( 
	array(0.05, 0.95),
	array(0.1, 0.9),
	array(0.2, 0.8),
	array(0.25, 0.75),
	array(0.45, 0.55),
	array(0.5, 0.5),
	array(0.55, 0.45), 
	array(0.85, 0.15),
	array(0.9, 0.1),
	array(0.95, 0.05)
);


var_dump(kMeans($data, 3, WITH_NORMALIZE));

function initialiseCentroids(array $data, $k, $normalize = false) {
	$dimensions = count($data[0]);
	$centroids = array();
	$dimmax = array();
	$dimmin = array(); 
	foreach($data as $document) {
		foreach($document as $dim => $val) {
			if(!isset($dimmax[$dim]) || $val > $dimmax[$dim]) {
				$dimmax[$dim] = $val;
			}
			if(!isset($dimmin[$dim]) || $val < $dimmin[$dim]) {
				$dimmin[$dim] = $val;
			}
		}
	}
	for($i = 0; $i < $k; $i++) {
		$centroids[$i] = initialiseCentroid($dimensions, $dimmax, $dimmin, $normalize);
	}
	return $centroids;
}

function initialiseCentroid($dimensions, $dimmax, $dimmin, $normalize = false) {
	$total = 0;
	$centroid = array();
	for($j = 0; $j < $dimensions; $j++) {
		$total += $centroid[$j] = (rand($dimmin[$j] * 1000, $dimmax[$j] * 1000));
	}

	$centroid = ( false === $normalize ? $centroid : normaliseValue($centroid, $total) );

	return $centroid;
}

function kMeans($data, $k, $normalize = false) {
	$centroids = initialiseCentroids($data, $k, $normalize = false);
	$mapping = array();

	while(true) {
		$new_mapping = assignCentroids($data, $centroids);
		foreach($new_mapping as $documentID => $centroidID) {
			if(!isset($mapping[$documentID]) || $centroidID != $mapping[$documentID]) {
				$mapping = $new_mapping;
				break;
			} else {
				return formatResults($mapping, $data, $centroids); 
			}
		}
		$centroids  = updateCentroids($mapping, $data, $k); 
	}
}

function formatResults($mapping, $data, $centroids) {
	$result  = array();
	$result['centroids'] = $centroids;
	foreach($mapping as $documentID => $centroidID) {
		$result[$centroidID][] = implode(',', $data[$documentID]);
	}
	return $result;
}

function assignCentroids($data, $centroids) {
	$mapping = array();

	foreach($data as $documentID => $document) {
		$minDist = 100;
		$minCentroid = null;
		foreach($centroids as $centroidID => $centroid) {
			$dist = 0;
			foreach($centroid as $dim => $value) {
				$dist += abs($value - $document[$dim]);
			}
			if($dist < $minDist) {
				$minDist = $dist;
				$minCentroid = $centroidID;
			}
		}
		$mapping[$documentID] = $minCentroid;
	}

	return $mapping;
}

function updateCentroids($mapping, $data, $k) {
	$centroids = array();
	$counts = array_count_values($mapping);

	foreach($mapping as $documentID => $centroidID) {
		foreach($data[$documentID] as $dim => $value) {
			$centroids[$centroidID][$dim] += ($value/$counts[$centroidID]); 
		}
	}

	if(count($centroids) < $k) {
		$centroids = array_merge($centroids, initialiseCentroids($data, $k - count($centroids)));
	}

	return $centroids;
}

function normaliseValue(array $vector, $total) {
	foreach($vector as &$value) {
		$value = $value/$total;
	}
	return $vector;
}

