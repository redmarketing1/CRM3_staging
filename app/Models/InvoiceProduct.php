<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CarDealership\Entities\DealershipProduct;
use Modules\Newspaper\Entities\Newspaper;
use Modules\Fleet\Entities\VehicleInvoice;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Modules\Taskly\Entities\ProjectProgress;
use Modules\Taskly\Entities\ProjectProgressFiles;  

class InvoiceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type',
        'product_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'description',
        'total',
        'product_name',
		'item',
		'price',
		'total_price',
		'progress',
		'progress_amount',
    ];
    public function product()
    {
        $invoice =  $this->hasMany(Invoice::class, 'id', 'invoice_id')->first();
        if (!empty($invoice) && $invoice->invoice_module == "account" || !empty($invoice) && $invoice->invoice_module == "machinerepair" || !empty($invoice) && $invoice->invoice_module == "sales" || $invoice->invoice_module == 'musicinstitute'|| $invoice->invoice_module == 'mobileservice' || $invoice->invoice_module == 'vehicleinspection' )  {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Modules\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "taskly") {
            if (module_is_active('Taskly')) {
                return  $this->hasOne(\Modules\Taskly\Entities\Task::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "cmms") {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Modules\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "rent") {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Modules\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "lms" ) {
            return $this->hasOne(\Modules\LMS\Entities\Course::class, 'id', 'product_id')->first();
        } elseif (!empty($invoice) && $invoice->invoice_module == "cardealership" ) {

            return $this->hasOne(DealershipProduct::class, 'id', 'product_id')->first();
        } elseif (!empty($invoice) && $invoice->invoice_module == "newspaper" ) {

            return $this->hasOne(Newspaper::class, 'id', 'product_id')->first();

        }elseif (!empty($invoice) && $invoice->invoice_module == "Fleet" ) {
            return VehicleInvoice::where('invoice_id',$this->invoice_id)->where('item',$this->product_id)->first();
        }
    }

	public function invoice_data()
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

	public function estimation_product()
    {
		$estimation_id = $this->invoice_data->project_estimation_id;
        return $this->hasOne(ProjectEstimationProduct::class, 'name', 'item')->where('project_estimation_id', $estimation_id);
    }

	public function project_progress()
    {
		$estimation_id = $this->invoice_data->project_estimation_id;
		$product_id = $this->estimation_product->id;

		return ProjectProgress::where('product_id',$product_id)->where('estimation_id',$estimation_id)->orderBy("id", "desc")->first();
    }

    public function project_all_progress()
    {
		$estimation_id = $this->invoice_data->project_estimation_id;
		$product_id = $this->estimation_product->id;

		return ProjectProgress::where('product_id',$product_id)->where('estimation_id',$estimation_id)->orderBy("id", "desc")->get();
    }

    public function progress_files(){
		$estimation_id = $this->invoice_data->project_estimation_id;
		$product_id = $this->estimation_product->id;
		return ProjectProgressFiles::where('product_id',$product_id)->where('estimation_id',$estimation_id)->orderBy("id", "desc")->get();
    }
}
