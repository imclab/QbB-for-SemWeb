<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';

class SimpleGraphTest extends PHPUnit_Framework_TestCase {
    var $_single_triple =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred>foo</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_single_triple_turtle =  '@prefix ex: <http://example.org/> .
     <http://example.org/subj> ex:pred "foo" .';

    var $_single_triple_json = '{ "http:\/\/example.org\/subj" : {"http:\/\/example.org\/pred" : [ { "value" : "foo", "type" : "literal" } ] } }';



  function test_add_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_sets_object_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'iri', $triples[0]['o_type']);
  }

  function test_add_resource_triple_ignores_duplicates() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');


    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_accepts_bnode_subjects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('_:subj', 'http://example.org/pred', 'http://example.org/obj');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_accepts_bnode_objects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', '_:obj');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_sets_bnode_object_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', '_:obj');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'bnode', $triples[0]['o_type']);
  }

  function test_add_literal_triple() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_literal_triple_sets_object_type() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'literal', $triples[0]['o_type']);
  }

  function test_add_literal_triple_sets_object_language() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_lang']));
    $this->assertEquals('en', $triples[0]['o_lang']);
  }
  function test_add_literal_triple_sets_object_datatype() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en', 'http://example.org/dt');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_datatype']));
    $this->assertEquals('http://example.org/dt', $triples[0]['o_datatype']);
  }

  function test_add_resource_triple_ignores_duplicate_languages() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'de');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');


    $this->assertEquals( 2, count($g->get_triples()));
  }

  function test_add_resource_triple_ignores_duplicate_datatypes() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt2');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt');


    $this->assertEquals( 2, count($g->get_triples()));
  }

  function test_get_first_literal() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "literal", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred'));
  }
  function test_get_first_literal_ignores_resources() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "literal", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred'));
  }

  function test_remove_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_literal_triple() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');
    $this->assertEquals( 0, count($g->get_triples()));
  }


  function test_remove_triples_about() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $g->remove_triples_about('http://example.org/subj');

    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_triples_about_affects_only_specified_subject() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'literal');

    $g->remove_triples_about('http://example.org/subj');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_from_rdfxml() {
    $g = new SimpleGraph();
    $g->from_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_from_rdfxml_replaces_existing_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->from_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_has_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1');

    $this->assertTrue( $g->has_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj2'));
  }
  function test_get_first_resource() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( "http://example.org/obj", $g->get_first_resource('http://example.org/subj', 'http://example.org/pred'));
  }
  function test_get_first_resource_ignores_literals() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "http://example.org/obj", $g->get_first_resource('http://example.org/subj', 'http://example.org/pred'));
  }


  function test_remove_property_values() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_property_values_removes_multiple_values() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj3');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj4');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj5');

    $this->assertEquals( 5, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_property_values_ignores_unknown_properties() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred2');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_remove_all_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');

    $this->assertEquals( 2, count($g->get_triples()));

    $g->remove_all_triples();
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_add_rdfxml_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_rdfxml($this->_single_triple);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }

  function test_add_rdfxml_ignores_duplicate_triples() {
    $g = new SimpleGraph();
    $g->from_rdfxml($this->_single_triple);
    $g->add_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

  }


  function test_add_turtle_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_turtle($this->_single_triple_turtle);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }

  function test_from_turtle() {
    $g = new SimpleGraph();
    $g->from_turtle($this->_single_triple_turtle);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_from_json() {
    $g = new SimpleGraph();
    $g->from_json($this->_single_triple_json);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }
  
  function test_add_json_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_json($this->_single_triple_json);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }  


  function test_is_empty() {
    $g = new SimpleGraph();

    $this->assertTrue( $g->is_empty() );
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');

    $this->assertFalse( $g->is_empty() );
    
  }

  function test_from_turtle_parses_datatypes() {
    
    $g = new SimpleGraph();
    $g->from_turtle('<http://example.org/subj> <http://example.org/pred> "1390"^^<http://www.w3.org/2001/XMLSchema#gYear> .');
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("1390", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("http://www.w3.org/2001/XMLSchema#gYear", $index['http://example.org/subj']['http://example.org/pred'][0]['datatype']);
  }
  
  function test_qname_to_uri() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals("http://example.org/foo", $g->qname_to_uri('ex:foo'));
  }

  function test_qname_to_uri_returns_null_if_no_match() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->qname_to_uri('bar:foo'));
  }

  function test_uri_to_qname() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals("ex:foo", $g->uri_to_qname('http://example.org/foo'));
  }

  function test_uri_to_qname_returns_null_if_no_match() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->uri_to_qname('http://example.blah/'));
  }

  function test_uri_to_qname_returns_null_if_uri_not_representable_as_qname() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->uri_to_qname('http://example.org/foo/'));
  }

  function test_get_first_literal_uses_preferred_language() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'en', 'en');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'fr', 'fr');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'de', 'de');

    $this->assertEquals( "en", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'en'));
    $this->assertEquals( "fr", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'fr'));
    $this->assertEquals( "de", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'de'));
  }
  
  function test_get_subjects_of_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_resource_triple('http://example.org/subj2', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_2');
    $g->add_resource_triple('http://example.org/subj3', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_literal_triple('http://example.org/subj4', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    
    $subjects = $g->get_subjects_of_type('http://example.org/type_1');
    $this->assertEquals(2, count($subjects), 'The returned subjects should be exactly 2');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj3', $subjects, 'subj3 matches and should be returned');
    $this->assertNotContains('http://example.org/subj2', $subjects, 'subj2 does not match and should not be returned');
    $this->assertNotContains('http://example.org/subj4', $subjects, 'subj4 does not match and should not be returned');
  }

  function test_get_subjects_where_resource() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj2');

    $g->add_resource_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_literal_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred', 'http://example.org/obj1');
    $this->assertEquals(2, count($subjects), 'The returned subjects should be exactly 2');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj2', $subjects, 'subj2 matches and should be returned');
    $this->assertNotContains('http://example.org/subj3', $subjects, 'subj3 does not match and should not be returned');
  }
  
  function test_get_subjects_where_resource_no_match_on_predicate() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred_foo', 'http://example.org/obj1');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }
  
  function test_get_subjects_where_resource_no_match_on_object() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred', 'http://example.org/obj_foo');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }
  
  function test_get_subjects_where_literal() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj2');

    $g->add_resource_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_literal_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred', 'http://example.org/obj1');
    $this->assertEquals(2, count($subjects), 'The returned subjects should be exactly 2');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj2', $subjects, 'subj2 matches and should be returned');
    $this->assertNotContains('http://example.org/subj3', $subjects, 'subj3 does not match and should not be returned');
  }
  
  function test_get_subjects_where_literal_no_match_on_predicate() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred_foo', 'http://example.org/obj1');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }
  
  function test_get_subjects_where_literal_no_match_on_object() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred', 'http://example.org/obj_foo');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }
  
  function test_reify(){

    $triple = array(
      '#foo' => array('#knows' => array(array('type'=>'uri','value' =>'#bar'))),
      );
      $RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    $expected = array(
      '_:Statement1' => array(
        $RDF.'type' => array(
          array(
              'type' => 'uri',
              'value' => $RDF.'Statement',
            )
          ),
        $RDF.'subject' => array(
            array(
                'type' => 'uri',
                'value' => '#foo',
              )
          ),
        $RDF.'predicate' => array(
            array(
                'type' => 'uri',
                'value' => '#knows',
              )
          ),
        $RDF.'object' => array(
            array(
                'type' => 'uri',
                'value' => '#bar',
              )
          ),
        
        )
      );
    $actual = SimpleGraph::reify($triple);
    
    $this->assertEquals($expected, $actual);
  }
  
  function test_diff_static_call(){
  
    $_1 = array(
      '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithA')), '#foo' => array(array('value'=>'foo')) )
      );  
    
    $_2 = array(
        '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithAlexander')), '#foo' => array(array('value'=>'foo')) )
        );
    $expected = array(
          '#x' => array( '#nick'=> array(array('value'=> 'keithA'))),
          );
    $actual = SimpleGraph::diff($_1,$_2);

    $this->assertEquals( $expected, $actual);
  }
  
  function test_diff_object_call(){
  
    $_1 = array(
      '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithA')), '#foo' => array(array('value'=>'foo')) )
      );  
    
    $_2 = array(
        '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithAlexander')), '#foo' => array(array('value'=>'foo')) )
        );
    $expected = array(
          '#x' => array( '#nick'=> array(array('value'=> 'keithA'))),
          );
    $object = new SimpleGraph($_1);
    $actual = $object->diff($_2);
    $this->assertEquals( $expected, $actual);
  }
  
  function test_merge_static(){
    
    $g1 = array(            //uri
      '#x' => array(            //prop
          'name' => array(        //obj
            array(
            'value' => 'Joe',
            'type' => 'literal',
              ),
            ),        //obj
        ),          //prop
      '_:y' => array(
          'name' => array(array(
            'value' => 'Joan',
            'type' => 'literal',
            ),),
        ),

      );

      $g2 = array(
        '#x' => array(
            'knows' => array( array(
              'value' => '_:y',
              'type' => 'bnode',
              ),
            ),
          ),

        '_:y' => array(
            'name' => array(
              array(
              'value' => 'Susan',
              'type' => 'literal',
              ),
              ),
          ),

        );

      $g3 = array (
        '#x' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Joe',
              'type' => 'literal',
            ),
          ),
          'knows' => 
          array (
            0 => 
            array (
              'value' => '_:y1',
              'type' => 'bnode',
            ),
          ),
        ),
        '_:y' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Joan',
              'type' => 'literal',
            ),
          ),
        ),
        '_:y1' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Susan',
              'type' => 'literal',
            ),
          ),
        ),
      );

    $g4 = array(
      '#x' => array('#knows' => array(
        'type' => 'uri',
        'value' => 'Me'
        ),
      ),
      );

    $r1 = (SimpleGraph::merge($g1,$g2));
    $this->assertEquals($r1, $g3);
  }


  function test_merge_object_call(){
    
    $g1 = array(            //uri
      '#x' => array(            //prop
          'name' => array(        //obj
            array(
            'value' => 'Joe',
            'type' => 'literal',
              ),
            ),        //obj
        ),          //prop
      '_:y' => array(
          'name' => array(array(
            'value' => 'Joan',
            'type' => 'literal',
            ),),
        ),

      );

      $g2 = array(
        '#x' => array(
            'knows' => array( array(
              'value' => '_:y',
              'type' => 'bnode',
              ),
            ),
          ),

        '_:y' => array(
            'name' => array(
              array(
              'value' => 'Susan',
              'type' => 'literal',
              ),
              ),
          ),

        );

      $g3 = array (
        '#x' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Joe',
              'type' => 'literal',
            ),
          ),
          'knows' => 
          array (
            0 => 
            array (
              'value' => '_:y1',
              'type' => 'bnode',
            ),
          ),
        ),
        '_:y' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Joan',
              'type' => 'literal',
            ),
          ),
        ),
        '_:y1' => 
        array (
          'name' => 
          array (
            0 => 
            array (
              'value' => 'Susan',
              'type' => 'literal',
            ),
          ),
        ),
      );

    $g4 = array(
      '#x' => array('#knows' => array(
        'type' => 'uri',
        'value' => 'Me'
        ),
      ),
      );
    $graph = new SimpleGraph($g1);
    $r1 = ($graph->merge($g2));
    $this->assertEquals($r1, $g3);
  }


}
?>
