<?php

namespace Modules\Taskly\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class ProjectEstimationExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    use Exportable;

    public function __construct($estimation_result, $quotations_details, $type){
        $this->export_estimation_result = $estimation_result;
        $this->quotations_details = $quotations_details;
        $this->type = $type;
    }

    public function collection(){
        $data = array();
        $i = 0;
        foreach ($this->export_estimation_result as $k => $row) {
            $data[$i]['pos'] = $row['pos'];
            $data[$i]['group_name'] = $row['group_name'];
            $data[$i]['name'] = $row['name'];
            $data[$i]['description'] = $row['description'];
            $data[$i]['quantity'] = $row['quantity'];
            $data[$i]['unit'] = $row['unit'];
            $data[$i]['optional'] = ($row['optional'] == 1) ? 1 : '0';
            foreach ($row['quote_lists'] as $key => $qrow) {
                $data[$i]['single_price' . $key] = $qrow['single_price'];
                $data[$i]['total_price' . $key] = $qrow['total_price'];
            }
            $i++;
        }
        return collect($data);
    }

    public function headings(): array
    {
        $empty_row = [' '];
        $row1_title = [' ', ' ', ' ', ' ', ' ', ' ', ' '];
        $net_incl_discount_title = [' ', ' ', ' ', ' ', ' ', ' ', __('Net incl. Discount'), ' '];
        $gross_incl_discount_title = [' ', ' ', ' ', ' ', ' ', ' ', __('Gross incl. Discount'), ' '];
        $net_title = [' ', ' ', ' ', ' ', ' ', ' ', __('Net'), ' '];
        $gross_title = [' ', ' ', ' ', ' ', ' ', ' ', __('Gross (incl. VAT)')];
        $markup_discount_title_empty = [' ', ' ', ' ', ' ', ' ', ' ', ' '];
        $markup_discount_val_empty = [' ', ' ', ' ', ' ', ' ', ' ', ' '];
        $main_title = [__("Pos"), __("Group Name"), __("Name"), __("Description"), __("Quantity"), __("Unit"), __("Optional")];
        $quotation_title = array();
        $row1_quotation_title = array();
        $net_incl_discount_value = array();
        $gross_incl_discount_value = array();
        $net_value = array();
        $gross_value = array();
        $markup_discount_title = array();
        $markup_discount_value = array();
        if ((isset($this->type) && $this->type == 'attachment-download') || (isset($this->type) && $this->type == 'attach')) {
            $vat_amount_title = [' ', ' ', ' ', ' ', ' ', ' ', __('VAT'), ' '];
            $cash_discount_amount_title = [' ', ' ', ' ', ' ', ' ', ' ', __('Cash Discount'), ' '];
            $vat_amount_value = array();
            $discount_amount_value = array();
        }
        if (!empty($this->quotations_details)) {
            foreach ($this->quotations_details as $row) {
                $net_incl_discount_value[] = $row['net_with_discount'];
                $net_incl_discount_value[] = '';
                $gross_incl_discount_value[] = $row['gross_with_discount'];
                $gross_incl_discount_value[] = '';
                $net_value[] = $row['net'];
                $net_value[] = '';
                $gross_value[] = $row['tax'] . '%';
                $gross_value[] = $row['gross'];
                if ((isset($this->type) && $this->type == 'attachment-download') || (isset($this->type) && $this->type == 'attach')) {
                    $markup_discount_title[] = '';
                    $markup_discount_value[] = '';
                    $vat_amount_value[] = $row['vat_amount'];
                    $discount_amount_value[] = $row['discount_amount'];
                } else {
                    $markup_discount_title[] = 'Markup';
                    $markup_discount_value[] = $row['markup'];
                }
                $markup_discount_title[] = 'Discount';
                $markup_discount_value[] = $row['discount'];
                $row1_quotation_title[] = $row['title'];
                $row1_quotation_title[] = '';
                $quotation_title[] = __('Single Price');
                $quotation_title[] = __('Total Price');
            }
        }
        $row1 = array_merge($row1_title, $row1_quotation_title);
        $row2 = array_merge($main_title, $quotation_title);
        $net_incl_discount = array_merge($net_incl_discount_title, $net_incl_discount_value);
        $gross_incl_discount = array_merge($gross_incl_discount_title, $gross_incl_discount_value);
        $net = array_merge($net_title, $net_value);
        $gross = array_merge($gross_title, $gross_value);
        $markup_discount = array_merge($markup_discount_title_empty, $markup_discount_title);
        $markup_discount_val = array_merge($markup_discount_val_empty, $markup_discount_value);
        if ((isset($this->type) && $this->type == 'attachment-download') || (isset($this->type) && $this->type == 'attach')) {
            $vat_amount = array_merge($vat_amount_title, $vat_amount_value);
            $discount_amount = array_merge($cash_discount_amount_title, $discount_amount_value);
            return [$empty_row, $net_incl_discount, $gross_incl_discount, $net, $gross, $vat_amount, $discount_amount, $markup_discount, $markup_discount_val, $empty_row, $row1, $row2];
        } else {
            return [$empty_row, $net_incl_discount, $gross_incl_discount, $net, $gross, $markup_discount, $markup_discount_val, $empty_row, $row1, $row2];
        }
    }
}
