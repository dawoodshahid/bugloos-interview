<?php

namespace DawoodShahid\GridView;

use Illuminate\Support\Arr;
use View;

class GridView
{
    protected $data, $fields, $sortField, $sortType, $searchableColumns, $pageSize = 10, $currentPage = 0, $lastPage = 0;

    public function config(Array $config)
    {
        if(isset($config['pageSize']))
        {
            $this->pageSize = (int)$config['pageSize'];
        }

        return $this;
    }

    public function data($data)
    {
        $this->data = $data;
        return $this;
    }

    public function fields($fields)
	{
        $validFields = ['label', 'type', 'sanitize', 'options'];

		foreach ($fields as $key => &$value) 
        {
			if (is_string($value)) 
            {
				$value = [
					'label' => $value,
					'alias' => $key,
				];
			} 
            else 
            {
                foreach($value as $k => $v)
                {
                    if(!in_array($k, $validFields))
                    {
                        unset($value[$k]);
                    }
                }

				if (!isset($value['label']))
                {
                    $value['label'] = ucwords(str_replace('_', ' ', $key));
                }
					
				$value['alias'] = $key;
			}

            $value['type'] = isset($value['type']) ? $value['type'] : 'string';
            $value['options'] = isset($value['options']) ? $value['options'] : [];
			$value['sanitize'] = array_key_exists('sanitize', $value) ? $value['sanitize'] : true;

            $this->validateType($key, $value['type']);

			$fields[$key] = $value;
		}

		$this->fields = $fields;

		$this->checkHasDoubleAlias();
		return $this;
	}

    public function sort($field, $type)
    {
        $type = strtolower($type);

        if($type != "desc" && $type != "asc")
        {
            throw new \Exception('Invalid sort type detected. Valid Types are: DESC, ASC');
        }

        $validFields = array_keys($this->fields);
        if(!in_array($field, $validFields))
        {
            throw new \Exception('Invalid field detected for sorting. Valid fields are: ' . implode(',', $validFields));
        }

        $this->sortField = $field;
        $this->sortType = $type;

        return $this;
    }

    public function getData()
    {
        $data = $this->make();
        return $data;
    }

    public function generateView()
    {
        $data = $this->make();

        return View::make('grid_view::index', [
            'data' => $data
        ]);
    }

    public function searchableColumns(Array $columns)
    {
        $validFields = array_keys($this->fields);

        if(count(array_diff($columns, $validFields)) > 0)
        {
            throw new \Exception('Invalid searchable column/s detected. Valid columns are: ' . implode(',', $validFields));
        }
        
        $this->searchableColumns = $columns;

        return $this;
    }

    public function page($page)
    {
        $this->currentPage = $page - 1;

        return $this;
    }

    private function formatData($format, $value)
    {
        settype($value, $format);

        return $value;
    }

    private function validateType($key, $type)
    {
        $validTypes = ["string", "integer", "float", "boolean", "array"];

        if(!in_array($type, $validTypes))
        {
            throw new \Exception('You have invalid data type for "' . $key . '". Valid Types are: ' . implode(',', $validTypes));
        }
    }

    private function checkHasDoubleAlias()
	{
		$aliases = [];
		foreach ($this->fields as $field) 
        {
			$fieldAlias = $field['alias'];

			if (isset($aliases[$fieldAlias]))
            {
                throw new \Exception('You have double alias on query. The field doubled is "' . $fieldAlias . '". Please, define the field with another alias.');
            }
			else
            {
                $aliases[$fieldAlias] = true;
            }
		}
	}

    private function make()
    {
        $validFields = array_keys($this->fields);
        $finalData = [];

        foreach($this->data as $row)
        {
            $temp = [];

            foreach($this->fields as $key => $value)
            {
                $temp[$key] = "";
            }

            foreach($row as $key => $value)
            {
                if(in_array($key, $validFields))
                {
                    $temp[$key] = $this->formatData($this->fields[$key]['type'], $value);

                    if(count($this->fields[$key]['options']) > 0)
                    {
                        $temp[$key] = isset($this->fields[$key]['options'][$temp[$key]]) ? $this->fields[$key]['options'][$temp[$key]] : "-";
                    }
                }
            }
            
            if(count($temp) > 0)
            {
                $finalData[] = $temp;
            }
        }

        if(count($finalData) > 0 && $this->sortField != '' && $this->sortType != '')
        {
            usort($finalData, function($a, $b) 
            {
                if($this->sortType == 'asc')
                {
                    return $a[$this->sortField] <=> $b[$this->sortField];
                }
                else
                {
                    return $b[$this->sortField] <=> $a[$this->sortField];
                }
            });
        }

        $this->lastPage = (int)ceil(count($finalData) / $this->pageSize);

        $headers = [];
        foreach($this->fields as $key => $value)
        {
            $headers[$key] = $value['label'];
        }

        $temp = [];
        $temp['data'] = $finalData;

        if(count($finalData) > $this->pageSize)
        {
            $temp['data'] = array_slice($finalData, ($this->pageSize * $this->currentPage), $this->pageSize);
        }

        $temp['pageSize'] = $this->pageSize;
        $temp['currentPage'] = $this->currentPage + 1;
        $temp['lastPage'] = $this->lastPage;
        $temp['header'] = $headers;
        $temp['searchableColumns'] = $this->searchableColumns;

        return $temp;
    }
}