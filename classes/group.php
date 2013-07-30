<?php namespace Table;

/**
 * Part of the fuel-Table-package
 *
 * @package     Table
 * @namespace   Table
 * @version     0.1-dev
 * @author      Gasoline Development Team
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2013 Gasoline Development Team
 * @copyright  2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

use ArrayAccess;
use Countable;
use Iterator;

abstract class Group implements ArrayAccess, Countable, Iterator {
    
    /**
     * Keeps the group's tag like e.g., 'thead', 'tbody', or 'tfoot'
     * 
     * Must be implemented by the respective group itself
     * 
     * @access  protected
     * @var     string
     */
    // protected $_group_tag;
    
    /**
     * Keeps the html-attributes of the group
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * Keeps the rows added to the group
     * 
     * @access  protected
     * @var     array
     */
    protected $_rows = array();
    
    /**
     * For ArrayAccess
     * 
     * @access  protected
     * @var     integer
     */
    protected $_curr_row = 0;
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Forge a new table-group with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $columns        An array of columns to use
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{group_tag}>'
     */
    public static function forge(array $columns = array(), array $attributes = array())
    {
        return new static($columns, $attributes);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Create a new table-group with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $columns        An array of columns to use
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{group_tag}>'
     */
    public function __construct(array $columns = array(), array $attributes = array())
    {
        $this->_attributes = $attributes;
        
        $columns && $this->add_cells($columns);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Renders the group's content
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-group with rows
     */
    public function render()
    {
        return html_tag(
            $this->_group_tag,
            $this->_attributes,
            ( $this->_rows
                ? implode(
                    PHP_EOL,
                    array_map(
                        function($row)
                        {
                            return $row->render();
                        },
                        $this->_rows
                    )
                )
                : ''
            )
        );
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an attribute of the group
     * 
     * @access  public
     * 
     * @param   string      $attribute  The attr
     * @param   string      $value      The value of the attribute to set
     * @param   boolean     $mode       The mode of setting the attribute. If
     *                                  omitted, $value will be set as the only
     *                                  value for the attribute. If set to 1,
     *                                  $value will be appended, if set to -1
     *                                  it will be prepended.
     *                                  Defaults to false i.e., overwrite
     * 
     * @return  \Table\Group
     */
    public function set($attribute, $value = null, $mode = false)
    {
        // Prepend?
        if ( $mode === -1 )
        {
            Helpers::add_attribute($this->_attributes, $attribute, $value, true);
        }
        // Any other case we will append
        elseif ( $mode === 1 )
        {
            Helpers::add_attribute($this->_attributes, $attribute, $value, false);
        }
        // Not adding, but setting i.e., replacing
        else
        {
            $this->_attributes[$attribute] = $value;
        }
        
        // And a chainable return
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get a property from the group. Either an attribute or a row
     * 
     * @param   string  $property   The property to get. Can be 'row' or 'row_N'
     *                              to return the last or N-th row.  If it does not
     *                              match, then $property is assumed an attribute
     * @param   mixed   $default    The default value to return if no matching
     *                              attribute was found. In case $property == 'row',
     *                              $default can be used to indicate the number of
     *                              the row to get
     * 
     * @return  mixed   Returns the value of $property, if a row then \Table\Row_{group_tag}
     */
    public function get($property, $default = null)
    {
        // Match magic properties starting with 'row'
        if ( 0 === strpos('row', $property) )
        {
            // Get the offset. Either $property was 'row_N' and we want 'N' or it
            //  was 'row' and then we will use $default as the offset
            $offset = ( false !== strpos('row_', $property) ? substr($property, 4) : ( $default ? : count($this->_rows) - 1 ) );
            
            // Use ArrayAccess to return
            return $this[$offset];
        }
        
        // Assume an attribute, so return that one (if found, otherwise $default)
        return array_key_exists($property, $this->_attributes) ? $this->_attributes[$property] : $default;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add an attribute to the array of attributes
     * 
     * @access  public
     * 
     * @param   string  $attribute  Name of the attribute to add e.g., 'class'
     * @param   mixed   $value      The value to set for $attribute
     * @param   boolean $prepend    Whether to prepend (false) or append (true)
     *                              $value to the classes' attributes.
     *                              Defaults to false
     * 
     * @return  \Table\Group
     */
    public function add($attribute, $value, $prepend = false)
    {
        return $this->set($attribute, $value, $prepend === false ? 1 : -1);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a row to the current group
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   array   $values     Array of values to add into the cells of the
     *                              row.
     * @param   array   $attributes Attributes to add to the row-opening tag
     * 
     * @return  \Table\Row          Returns the just created row-object
     */
    public function add_row(array $values = array(), array $attributes = array())
    {
        return $this->_rows[] = new Row(str_replace(__CLASS__ . '_', '', get_called_class()), $values, $attributes);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Append a cell to the current row
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   string  $value      The value of the cell
     * @param   array   $attributes Array of html-attributes of the cell
     * 
     * @return   \Table\Row
     */
    public function add_cell($value = '', array $attributes = array())
    {
        $this->_rows OR $this->add_row();
        
        return end($this->_rows)->add_cell($value, $attributes);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add multiple cells at once to the last row
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   array   $values     An array of values or an array of
     *                              value => attributes.
     * 
     * @return   \Table\Group
     */
    public function add_cells(array $values = array())
    {
        if ( ! $values )
        {
            return $this;
        }
        
        foreach ( $values as $value => $attributes )
        {
            if ( ! is_array($attributes) )
            {
                $value = $attributes;
                $attributes = array();
            }
            
            $this->add_cell($value, $attributes);
        }
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Remove an attribute's value
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute's name to remove
     * @param   string  $value      The value of the attribute to remove
     * 
     * @return  \Table\Group
     */
    public function remove($attribute, $value = null, $purge = false)
    {
        Helpers::remove_attribute($this->_attributes, $attribute, $value, $purge);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Clear an attribute i.e., remove it completely
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute's name to remove
     * 
     * @return  \Table\Group
     */
    public function clear($attribute)
    {
        return $this->remove($attribute, null, true);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Support echoing the group by using __toString as a wrapper for render()
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-group with rows
     */
    public function __toString()
    {
        return $this->render();
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic set to set attributes
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute to set
     * @param   string  $value      The value to set.  Defaults to null
     */
    public function __set($attribute, $value = null)
    {
        $this->set($attribute, $value);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic get that does nothing but return the classes' get() result
     * 
     * @access  public
     * @param   string  $property   The property to get
     * 
     * @return  mixed   Returns the value for $property, if not found null
     */
    public function __get($property)
    {
        return $this->get($property);
    }
    
    public function __call($method, $args = array())
    {
        if ( preg_match('/^s|get/', $method, $match) )
        {
            array_unshift($args, preg_replace('/^s|get_/', '', $method));
            
            return call_user_func_array(array($this, $match == 'set' ? 'set' : 'get'), $args);
        }
        
        // Throw an exception
        throw new BadMethodCallException('Call to undefined method ' . get_called_class() . '::' . $method . '()');
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Countable Interface
     */
    
    public function count()
    {
        return count($this->_rows);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    public function current()
    {
        return $this->_rows[$this->key()];
    }
    
    public function rewind()
    {
        $this->_curr_row = 0;
    }
    
    public function key()
    {
        return $this->_curr_row;
    }
    
    public function next()
    {
        ++$this->_curr_row;
    }
    
    public function valid()
    {
        return isset($this->_rows[$this->_curr_row]);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ArrayAccess Interface
     */
    
    public function offsetExists($offset)
    {
        return isset($this->_rows[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ( ! $this->offsetExists($offset) )
        {
            throw new OutOfBoundsException('Access to undefined row-index [' . $offset . ']');
        }
        
        return $this->_rows[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException('Cannot set row-index [' . $offset . '] as rows are read-only');
    }
    
    public function offsetUnset($offset)
    {
        if ( $this->offsetExists($offset) )
        {
            unset($this->_rows[$offset]);
        }
    }
    
}
