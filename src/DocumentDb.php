<?php

class DocumentDbException extends Exception {};
class NoDocumentFoundException extends DocumentDbException {};
class DocumentConflictException extends DocumentDbException {};


interface Fluid_DocumentDb {


	function set( $key, $tuple );


	function get( $key );


}
