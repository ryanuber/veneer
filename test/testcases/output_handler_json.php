<?php
class test_json_output extends PHPUnit_Framework_TestCase
{
    public function test_json_pretty_print()
    {
        $json_ugly = <<<EOF
{"item1":"value1","item2":"value2","item3":["list1","list2","list3"]}
EOF;

        $json_pretty_2sp = <<<EOF
{
  "item1":"value1",
  "item2":"value2",
  "item3":[
    "list1",
    "list2",
    "list3"
  ]
}
EOF;

        $json_pretty_4sp = <<<EOF
{
    "item1":"value1",
    "item2":"value2",
    "item3":[
        "list1",
        "list2",
        "list3"
    ]
}
EOF;

        $this->assertEquals(
            $json_pretty_2sp,
            \veneer\output\handler\json::jsonpp($json_ugly)
        );

        $this->assertEquals(
            $json_pretty_4sp,
            \veneer\output\handler\json::jsonpp($json_ugly, '    ')
        );
    }

    public function test_json_encode_array()
    {
        $json_pretty_mixed_data = <<<EOF
{
  "item1":"value1",
  "item2":"value2",
  "item3":[
    "list1",
    "list2",
    "list3"
  ],
  "item4":true,
  "item5":false,
  "item6":123
}
EOF;

        $json_array = array(
            'item1' => 'value1',
            'item2' => 'value2',
            'item3' => array('list1', 'list2', 'list3'),
            'item4' => true,
            'item5' => false,
            'item6' => 123
        );

        $this->assertEquals(
            $json_pretty_mixed_data,
            \veneer\output\handler\json::output_arr($json_array)
        );
    }

    public function test_json_encode_string()
    {
        $this->assertEquals(
            '"This is a string"',
            \veneer\output\handler\json::output_str('This is a string')
        );
    }

    public function test_json_headers()
    {
        $headers = array();
        foreach (\veneer\output\handler\json::headers() as $header) {
            array_push($headers, strtolower($header));
        }
        $this->assertTrue(in_array(
            'content-type: application/json',
            $headers
        ));
    }
}
