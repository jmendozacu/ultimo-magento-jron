<?php

class CsvWriter
{

	private $file;

	private $headLineNames;

	public function __construct( $fileName )
	{
		$this->file = new SplFileObject( $fileName, 'a' );
		$this->file->setCsvControl( ";" );
	}

	public function writeHeadLine( array $data )
	{
		$this->headLineNames = array_keys( $data );
		$this->file->fputcsv( $this->headLineNames );
	}

	public function writeDataLine( array $data )
	{
		$this->file->fputcsv( $this->sortDataByHeadLine( $data ) );
	}

	private function sortDataByHeadLine( array $data )
	{
		$sortedData = [ ];

		foreach ( $this->headLineNames as $attributeName )
		{
			$sortedData[] = $data[ $attributeName ];
		}

		return $sortedData;
	}
} 