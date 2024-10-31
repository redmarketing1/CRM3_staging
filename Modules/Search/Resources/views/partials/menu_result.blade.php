 <div class="search-result-item menu-result">
     <div class="card">
         <a href="{{ $route }}">
             <div class="d-flex align-items-center p-2">
                 <i class="menu-icon {{ $icon ?? 'fas fa-bars' }} mr-2"></i>
                 <div class="menu-content ">
                     @if (isset($parentName))
                         <h5 class="mb-0">{{ $name }}</h5>
                         <div class="sub-breadcrums">
                             <small class="text-muted">{{ $parentName }}</small>
                             <i class="fas fa-chevron-right mx-1 text-muted small"></i>
                             <small>{{ $name }}</small>
                         </div>
                     @else
                         <h5 class="mb-0">{{ $name }}</h5>
                         @if ($isParent)
                             <small class="text-muted"></small>
                         @endif
                     @endif
                 </div>
             </div>
         </a>
     </div>
 </div>
