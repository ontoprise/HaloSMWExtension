<?php

/**
 * Implementation of Smith-Waterman algorithm.
 *
 */
class SmithWaterman {

	// global state of algorithm
	private $gapPanelty;
	private $mismatchPanelty;
	private $matchPanelty;

	// local state
	private $seqA;
	private $seqB;

	private $MatrixRows;
	private $MatrixCols;

	private $Matrix;

	public function __construct($gapPanelty = -1, $mismatchPanelty = -1, $matchPanelty = 2) {

		$this->gapPanelty = $gapPanelty;
		$this->mismatchPanelty = $mismatchPanelty;
		$this->matchPanelty = $matchPanelty;

	}

	/*public function print_matrix( $seqRow, $seqCol ){
		$MaxValue = $this->max_in_matrix();
		echo( 'D - ' );
		for ( $i = 0; $i < strlen( $seqRow ); $i++ ){
		echo( $seqRow[ $i ] . ' ' );
		}

		echo( "\n" );

		$i = -1;
		echo( '- ' );
		foreach ( $this->Matrix as $row ){
		if ( $i != -1 ) echo( $seqCol[ $i ] . ' ' );
		$this->print_matrix_row( $row, $MaxValue );
		$i++;
		}
		}*/

	public function getBestMatches($seqA, $seqB) {
		$this->seqA = $seqA;
		$this->seqB = $seqB;
			
		$this->MatrixRows = strlen( $seqA ) + 1;
		$this->MatrixCols = strlen( $seqB ) + 1;

		$this->Matrix = array( array () );

		$this->smith_waterman();
		$TopValues = $this->get_positions_with_number();
		$results = array();
		foreach ( $TopValues as $Value ){
			$matchSeq = $this->trace_back_seq( $Value);
			$results[] = strrev($matchSeq[ 0 ]);
		}

		return $results;
	}

	private function smith_waterman( ){

		$this->init_matrix(0);

		for ( $i = 0; $i < strlen( $this->seqB ); $i++ ){
			for ( $j = 0; $j < strlen( $this->seqA ); $j++ ){

				$CaseArray = array( 0, 0, 0 );

				( $this->seqA[ $j ] == $this->seqB[ $i ] ) ? ( $CaseArray[ 0 ] = $this->Matrix[ $i ][ $j ] + $this->matchPanelty ) : ( $CaseArray[ 0 ] = $this->Matrix[ $i ][ $j ] + $this->mismatchPanelty );

				$CaseArray[ 1 ] = $this->Matrix[ $i ][ $j + 1 ] + $this->gapPanelty;
				$CaseArray[ 2 ] = $this->Matrix[ $i + 1 ][ $j ] + $this->gapPanelty;

				rsort( $CaseArray );

				( $CaseArray[ 0 ] >= 0 ) ? ( $this->Matrix[ $i + 1 ][ $j + 1 ] = $CaseArray[ 0 ] ) : ( $this->Matrix[ $i + 1][ $j + 1 ] = 0 );

			}

		}

	}

	private function get_positions_with_number(  ){
		$value = $this->max_in_matrix($this->Matrix);

		$result = array();
		$hits = 0;
		for ( $i = 0; $i < $this->MatrixCols-1; $i++ ){
			for ( $j = 0; $j < $this->MatrixRows-1; $j++ ){
				if ( $this->Matrix[ $i + 1 ][ $j + 1 ] == $value ) {
					$result[ $hits ] = array( $i + 1, $j + 1 );
					$hits++;
				}
			}
		}
		return( $result );
	}

	private function trace_back_seq( $Start ){
		$result = array( "", "");
		$i = 0;
		while ( $this->Matrix[ $Start[ 0 ] - $i ][ $Start[ 1 ] - $i ] != null ){
			$result[ 0 ] = $result[ 0 ] . $this->seqA[ $Start[ 1 ] - $i - 1] ;
			$result[ 1 ] = $result[ 1 ] . $this->seqB[ $Start[ 0 ] - $i - 1] ;
			$i++;
		}
		return( $result );
	}

	private function init_matrix( $InitValue ){
		for ( $i = 0; $i < $this->MatrixCols; $i++ ){
			for ( $j = 0; $j < $this->MatrixRows; $j++ ){
				$this->Matrix[ $i ][ $j ] = $InitValue;
			}
		}
	}

	private function max_in_matrix( ){
		$result = 0;
			
		for ( $i = 0; $i < $this->MatrixCols-1; $i++ ){
			for ( $j = 0; $j < $this->MatrixRows-1; $j++ ){
				if ( $this->Matrix[ $i + 1 ][ $j + 1 ] > $result ) { $result = $this->Matrix[ $i + 1 ][ $j + 1 ]; }
			}
		}

		return( $result );

	}
	private function print_matrix_row( $row, $MaxValue ){
		foreach ( $row as $value ){
			( $value == $MaxValue ) ? ( $output = '<' . $value . '>' ) : ( $output = $value . ' ' );
			echo( $output );
		}
		echo( "\n" );
	}


}

$mediaWikiLocation = dirname(__FILE__) . '/../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";


$qe = new QueryExpander();
$terms = array('phasen','modell', 'portal');
$maxs = $qe->findAggregatedTerms($terms);
$occ = array();
foreach($maxs as $m => $score) {
	foreach($terms as $t) {
        if (stripos($m, $t) !== false) {
        	if (!array_key_exists($m, $occ)) {
        		$occ[$m] = 0;
        	} else {
                $occ[$m]++;
        	}
        }
	}
}
foreach($occ as $t => $score) {
    if ($score > 0) print("[".$t."] $score");
}


?>