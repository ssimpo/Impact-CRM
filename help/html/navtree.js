var NAVTREE =
[
  [ "Impact CRM", "index.htm", [
    [ "Related Pages", "pages.htm", [
      [ "Todo List", "todo.htm", null ],
      [ "Deprecated List", "deprecated.htm", null ]
    ] ],
    [ "Data Structures", "annotated.htm", [
      [ "ACL", "classACL.htm", null ],
      [ "Calendar", "classCalendar.htm", null ],
      [ "Calendar_Supercass", "classCalendar__Supercass.htm", null ],
      [ "dateParser", "classdateParser.htm", null ],
      [ "dateParser_ISO8601Date", "classdateParser__ISO8601Date.htm", null ],
      [ "iCal_Interpreter", "classiCal__Interpreter.htm", null ],
      [ "Impact_Superclass", "classImpact__Superclass.htm", null ],
      [ "Plugin", "classPlugin.htm", null ],
      [ "repeatParser", "classrepeatParser.htm", null ],
      [ "SAVI_Parser", "classSAVI__Parser.htm", null ],
      [ "templater", "classtemplater.htm", null ]
    ] ],
    [ "Data Structure Index", "classes.htm", null ],
    [ "Data Fields", "functions.htm", null ],
    [ "Namespace List", "namespaces.htm", [
      [ "Calendar", "namespaceCalendar.htm", null ],
      [ "Database", "namespaceDatabase.htm", null ],
      [ "Impact", "namespaceImpact.htm", null ],
      [ "Templator", "namespaceTemplator.htm", null ]
    ] ],
    [ "File List", "files.htm", [
      [ "H:/Projects/ImpactCRM/ImpactCRM/controllers/main.php", "main_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/includes/functions.php", "functions_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.ACL.php", "class_8ACL_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.Calendar.php", "class_8Calendar_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.Database.php", "class_8Database_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.dateParser.php", "class_8dateParser_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.iCalInterpreter.php", "class_8iCalInterpreter_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.Impact.php", "class_8Impact_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.Plugin.php", "class_8Plugin_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.repeatParser.php", "class_8repeatParser_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.SAVI.php", "class_8SAVI_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/class.templater.php", "class_8templater_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/superclass.php", "superclass_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/Calendar/class.Calendar.Alarm.php", "class_8Calendar_8Alarm_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/Calendar/class.Calendar.Event.php", "class_8Calendar_8Event_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/Calendar/class.Calendar.Journal.php", "class_8Calendar_8Journal_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/Calendar/class.Calendar.Todo.php", "class_8Calendar_8Todo_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/Calendar/superclass.Calendar.php", "superclass_8Calendar_8php.htm", null ],
      [ "H:/Projects/ImpactCRM/ImpactCRM/models/dateParser/class.dateParser.ISO8601Date.php", "class_8dateParser_8ISO8601Date_8php.htm", null ]
    ] ],
    [ "Globals", "globals.htm", null ]
  ] ]
];

function createIndent(o,domNode,node,level)
{
  if (node.parentNode && node.parentNode.parentNode)
  {
    createIndent(o,domNode,node.parentNode,level+1);
  }
  var imgNode = document.createElement("img");
  if (level==0 && node.childrenData)
  {
    node.plus_img = imgNode;
    node.expandToggle = document.createElement("a");
    node.expandToggle.href = "javascript:void(0)";
    node.expandToggle.onclick = function() 
    {
      if (node.expanded) 
      {
        $(node.getChildrenUL()).slideUp("fast");
        if (node.isLast)
        {
          node.plus_img.src = node.relpath+"ftv2plastnode.png";
        }
        else
        {
          node.plus_img.src = node.relpath+"ftv2pnode.png";
        }
        node.expanded = false;
      } 
      else 
      {
        expandNode(o, node, false);
      }
    }
    node.expandToggle.appendChild(imgNode);
    domNode.appendChild(node.expandToggle);
  }
  else
  {
    domNode.appendChild(imgNode);
  }
  if (level==0)
  {
    if (node.isLast)
    {
      if (node.childrenData)
      {
        imgNode.src = node.relpath+"ftv2plastnode.png";
      }
      else
      {
        imgNode.src = node.relpath+"ftv2lastnode.png";
        domNode.appendChild(imgNode);
      }
    }
    else
    {
      if (node.childrenData)
      {
        imgNode.src = node.relpath+"ftv2pnode.png";
      }
      else
      {
        imgNode.src = node.relpath+"ftv2node.png";
        domNode.appendChild(imgNode);
      }
    }
  }
  else
  {
    if (node.isLast)
    {
      imgNode.src = node.relpath+"ftv2blank.png";
    }
    else
    {
      imgNode.src = node.relpath+"ftv2vertline.png";
    }
  }
  imgNode.border = "0";
}

function newNode(o, po, text, link, childrenData, lastNode)
{
  var node = new Object();
  node.children = Array();
  node.childrenData = childrenData;
  node.depth = po.depth + 1;
  node.relpath = po.relpath;
  node.isLast = lastNode;

  node.li = document.createElement("li");
  po.getChildrenUL().appendChild(node.li);
  node.parentNode = po;

  node.itemDiv = document.createElement("div");
  node.itemDiv.className = "item";

  node.labelSpan = document.createElement("span");
  node.labelSpan.className = "label";

  createIndent(o,node.itemDiv,node,0);
  node.itemDiv.appendChild(node.labelSpan);
  node.li.appendChild(node.itemDiv);

  var a = document.createElement("a");
  node.labelSpan.appendChild(a);
  node.label = document.createTextNode(text);
  a.appendChild(node.label);
  if (link) 
  {
    a.href = node.relpath+link;
  } 
  else 
  {
    if (childrenData != null) 
    {
      a.className = "nolink";
      a.href = "javascript:void(0)";
      a.onclick = node.expandToggle.onclick;
      node.expanded = false;
    }
  }

  node.childrenUL = null;
  node.getChildrenUL = function() 
  {
    if (!node.childrenUL) 
    {
      node.childrenUL = document.createElement("ul");
      node.childrenUL.className = "children_ul";
      node.childrenUL.style.display = "none";
      node.li.appendChild(node.childrenUL);
    }
    return node.childrenUL;
  };

  return node;
}

function showRoot()
{
  var headerHeight = $("#top").height();
  var footerHeight = $("#nav-path").height();
  var windowHeight = $(window).height() - headerHeight - footerHeight;
  navtree.scrollTo('#selected',0,{offset:-windowHeight/2});
}

function expandNode(o, node, imm)
{
  if (node.childrenData && !node.expanded) 
  {
    if (!node.childrenVisited) 
    {
      getNode(o, node);
    }
    if (imm)
    {
      $(node.getChildrenUL()).show();
    } 
    else 
    {
      $(node.getChildrenUL()).slideDown("fast",showRoot);
    }
    if (node.isLast)
    {
      node.plus_img.src = node.relpath+"ftv2mlastnode.png";
    }
    else
    {
      node.plus_img.src = node.relpath+"ftv2mnode.png";
    }
    node.expanded = true;
  }
}

function getNode(o, po)
{
  po.childrenVisited = true;
  var l = po.childrenData.length-1;
  for (var i in po.childrenData) 
  {
    var nodeData = po.childrenData[i];
    po.children[i] = newNode(o, po, nodeData[0], nodeData[1], nodeData[2],
        i==l);
  }
}

function findNavTreePage(url, data)
{
  var nodes = data;
  var result = null;
  for (var i in nodes) 
  {
    var d = nodes[i];
    if (d[1] == url) 
    {
      return new Array(i);
    }
    else if (d[2] != null) // array of children
    {
      result = findNavTreePage(url, d[2]);
      if (result != null) 
      {
        return (new Array(i).concat(result));
      }
    }
  }
  return null;
}

function initNavTree(toroot,relpath)
{
  var o = new Object();
  o.toroot = toroot;
  o.node = new Object();
  o.node.li = document.getElementById("nav-tree-contents");
  o.node.childrenData = NAVTREE;
  o.node.children = new Array();
  o.node.childrenUL = document.createElement("ul");
  o.node.getChildrenUL = function() { return o.node.childrenUL; };
  o.node.li.appendChild(o.node.childrenUL);
  o.node.depth = 0;
  o.node.relpath = relpath;

  getNode(o, o.node);

  o.breadcrumbs = findNavTreePage(toroot, NAVTREE);
  if (o.breadcrumbs == null)
  {
    o.breadcrumbs = findNavTreePage("index.html",NAVTREE);
  }
  if (o.breadcrumbs != null && o.breadcrumbs.length>0)
  {
    var p = o.node;
    for (var i in o.breadcrumbs) 
    {
      var j = o.breadcrumbs[i];
      p = p.children[j];
      expandNode(o,p,true);
    }
    p.itemDiv.className = p.itemDiv.className + " selected";
    p.itemDiv.id = "selected";
    $(window).load(showRoot);
  }
}

