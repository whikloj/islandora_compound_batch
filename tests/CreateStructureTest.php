<?php
namespace Drupal\islandora_batch_compound\tests;

use PHPUnit\Framework\TestCase;

class createStructureTest extends TestCase {

  private $RESOURCE_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'resources' .
  DIRECTORY_SEPARATOR . 'generate_structure';

  private $dom = NULL;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    if (is_null($this->dom)) {
      $this->dom = new \DOMDocument();
    }
  }
  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    self::deleteTreeContents($this->RESOURCE_DIR);
  }

  public function test_123() {
    $parts = array(
      1,
      2,
      3,
      4,
      5,
      6,
      7,
      8,
      9,
      10,
      11,
    );
    $compound = $this->generateCompound($parts);
    $expected = $parts;
    array_walk($expected, function(&$o) use ($compound) {
      $o = self::combinePath(array($compound, $o));
    });

    self::runGenerateStructure($this->RESOURCE_DIR);
    $structure = self::combinePath(array(
      $this->RESOURCE_DIR,
      $compound ,
      'structure.xml'
    ));
    $output = $this->parseStructures($structure);
    $this->assertEquals($expected, $output, 'ABC sort failed');
  }

  public function test_ABC() {
    $parts = array(
      'A',
      'B',
      'C',
      'D',
      'E',
      'F',
      'G',
      'H',
      'I',
      'J',
      'K',
    );
    $compound = $this->generateCompound($parts);
    $expected = $parts;
    array_walk($expected, function(&$o) use ($compound) {
      $o = self::combinePath(array($compound, $o));
    });

    self::runGenerateStructure($this->RESOURCE_DIR);
    $structure = self::combinePath(array(
      $this->RESOURCE_DIR,
      $compound ,
      'structure.xml'
    ));
    $output = $this->parseStructures($structure);
    $this->assertEquals($expected, $output, 'ABC sort failed');
  }

  public function test_FirstSecondThird() {
    $parts = array(
      'eighth',
      'eleventh',
      'fifth',
      'first',
      'fourth',
      'ninth',
      'second',
      'seventh',
      'sixth',
      'tenth',
      'third',
    );
    $compound = $this->generateCompound($parts);
    $expected = $parts;
    array_walk($expected, function(&$o) use ($compound) {
      $o = self::combinePath(array($compound, $o));
    });

    self::runGenerateStructure($this->RESOURCE_DIR);
    $structure = self::combinePath(array(
      $this->RESOURCE_DIR,
      $compound ,
      'structure.xml'
    ));
    $output = $this->parseStructures($structure);
    $this->assertEquals($expected, $output, 'ABC sort failed');
  }


  /**
   * Parse the structure.xml to get the children in order.
   * @param $filename
   *   The structures.xml filename
   * @return array
   *   The children attributes.
   */
  private function parseStructures($filename) {
    $this->dom->load($filename);
    $children = $this->dom->getElementsByTagName('child');
    $output = array();
    foreach ($children as $child) {
      $output[] = $child->attributes->getNamedItem('content')->value;
    }
    return $output;
  }

  /**
   * Utility for helping with separators.
   * @param array $pathParts
   *   parts of a path
   * @return string
   *   the combined path
   */
  protected static function combinePath(array $pathParts) {
    if (is_array($pathParts) && count($pathParts) > 0) {
      return implode(DIRECTORY_SEPARATOR, $pathParts);
    }
    else {
      return "";
    }
  }

  /**
   * Utility for the run create_structures_files.php
   *
   * @param $dir
   *   The directory to run against.
   */
  private static function runGenerateStructure($dir) {
    $command = "/usr/bin/env php " .
      realpath("extras/scripts/create_structure_files.php") .
      " " . $dir . " >/dev/null";
    exec($command);
  }

  /**
   * Generate a directory with fake compounds.
   *
   * @param array $parts
   *   Array of names for the compound parts.
   *
   * @return string
   *   The unique ID of the compound.
   */
  private function generateCompound(array $parts) {
    do {
      $id = uniqid();
      $output_dir = $this->RESOURCE_DIR . DIRECTORY_SEPARATOR . $id;
    } while (file_exists($output_dir));
    var_dump($output_dir);
    mkdir($output_dir);
    touch($output_dir . DIRECTORY_SEPARATOR . 'MODS.xml');
    foreach ($parts as $item) {
      $dir = $output_dir . DIRECTORY_SEPARATOR . $item;
      mkdir($dir);
      touch($dir . DIRECTORY_SEPARATOR . 'MODS.xml');
      touch($dir . DIRECTORY_SEPARATOR . 'OBJ.jp2');
    }
    return $id;
  }

  /**
   * Recursively delete all contents of the provided directory,
   *
   * @param $dir
   *   The directory to empty.
   */
  private static function deleteTreeContents($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      $current = $dir . DIRECTORY_SEPARATOR . $file;
      if (is_dir($current)){
        self::deleteTreeContents($current);
        rmdir($current);
      }
      elseif (is_file($current)) {
        unlink($current);
      }
    }
  }
}