
Simple XPath-like XML tree parsing and querying
===============================================

stick these files inside your PEAR folder, in the XML subfolder.

they are self-contained and require no other PEAR modules.

they don't really need PEAR at all - you can stick them anywhere 
in the include path, so long as they're in a folder called 'XML'.

use it like this:

  include_once("XML/NiceTree.php");

  $xml = "
    <document>
      <title id="9">Hello</title>
      <item>one</item>
      <item>two</item>
    </document>
  ";

  $tree = new XML_NiceTree($xml);

  $tree->findMulti('document/item'); # returns array of <item>s
  $tree->findSingle('document.title'); # returns first match
  $tree->findSingleContent('document/title'); # returns 'Hello'
  $tree->findSingleAttribute('document/title', 'id'); # returns '9'

and that's it!

--cal