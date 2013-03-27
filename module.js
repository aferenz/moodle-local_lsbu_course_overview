// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript for the LSBU Course Overview block
 *
 * @package    block
 * @subpackage lsbu_course_over
 * @copyright  2013 University of London Computer Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_lsbu_course_overview = {};

M.block_lsbu_course_overview.init_tree = function(Y, expand_all, htmlid) {

    Y.use('yui2-treeview', function(Y) {

        // 1. Fix to bug UALMOODLE-58: look for &amp; entity in label and replace with &. This is to fix a bug in YUI TreeView
        // 2. Focus the current node
        var current_node = null;

        function tree_traversal(node){
            if(node.hasChildren){
                var nodes = node.children;
                for(var i = 0; i < nodes.length; i++)    {
                    var test_node = nodes[i];
                    var label = test_node.label;
                    if(label){
                        var decoded = label.replace(/&amp;/g, '&');
                        test_node.label = decoded;
                    }

                    tree_traversal(test_node);
                }
            }
        }

        // Construct the tree
        var tree = new Y.YUI2.widget.TreeView(htmlid);

        // Now the tree has been constructed traverse it to correct duff HTML...
        var root = tree.getRoot();
        if(root) {
            var array = tree_traversal(root);
        }

        // The tree is not created in the DOM until this method is called:
        tree.render();

    });
};
