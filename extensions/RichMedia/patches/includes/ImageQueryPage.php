<?php

/**
 * Variant of QueryPage which uses a gallery to output results, thus
 * suited for reports generating images
 *
 * @ingroup SpecialPage
 * @author Rob Church <robchur@gmail.com>
 */
class ImageQueryPage extends QueryPage {

	/**
	 * Format and output report results using the given information plus
	 * OutputPage
	 *
	 * @param OutputPage $out OutputPage to print to
	 * @param Skin $skin User skin to use
	 * @param Database $dbr Database (read) connection to use
	 * @param int $res Result pointer
	 * @param int $num Number of available result rows
	 * @param int $offset Paging offset
	 */
	protected function outputResults( $out, $skin, $dbr, $res, $num, $offset ) {
		if( $num > 0 ) {
			$gallery = new ImageGallery();
			$gallery->useSkin( $skin );

			# $res might contain the whole 1,000 rows, so we read up to
			# $num [should update this to use a Pager]
			for( $i = 0; $i < $num && $row = $dbr->fetchObject( $res ); $i++ ) {
				$image = $this->prepareImage( $row );
				if( $image ) {
					$gallery->add( $image->getTitle(), $this->getCellHtml( $row ) );
				}
			}

			$out->addHtml( $gallery->toHtml() );
		}
	}

	/**
	 * Prepare an image object given a result row
	 *
	 * @param object $row Result row
	 * @return Image
	 */
	private function prepareImage( $row ) {
		/*op-patch|BL|2009-06-17|RichMedia|AdditionalNamespaceCheck|start*/
		// NS_IMAGE may not be the correct Namespace for this MIME type
		global $wgNamespaceByExtension;
		$namespace = isset( $row->namespace ) ? $row->namespace : NS_IMAGE;
		list( $partname, $ext ) = UploadForm::splitExtensions( $row->title );
		if( count( $ext ) ) {
			$finalExt = $ext[count( $ext ) - 1];
		} else {
			$finalExt = '';
		}
		if ( isset( $finalExt ) )
			if ( isset( $wgNamespaceByExtension[$finalExt] ) )
				$namespace = $wgNamespaceByExtension[$finalExt];
		/*op-patch|BL|2009-06-17|end*/
		$title = Title::makeTitleSafe( $namespace, $row->title );
		/*op-patch|BL|2009-06-17|RichMedia|AdditionalNamespaceCheck|start*/
		// NS_IMAGE is not the only Namespace now, so check them all
		// content was:
		return ( $title instanceof Title  && Namespace::isImage( $title->getNamespace() ) )
		/*op-patch|BL|2009-06-17|end*/
			? wfFindFile( $title )
			: null;
	}

	/**
	 * Get additional HTML to be shown in a results' cell
	 *
	 * @param object $row Result row
	 * @return string
	 */
	protected function getCellHtml( $row ) {
		return '';
	}

}
