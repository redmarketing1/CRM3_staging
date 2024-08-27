@if (isset($group['children']) && !empty($group['children']))
@php $i = 1; @endphp
@foreach($group['children'] as $erow)
                    <BoQCtgy RNoPart="{{ sprintf('%02d', $i) }}">
                        <LblTx>
                            <p>
                                <span>{{ $erow['group_name'] }}</span>
                            </p>
                        </LblTx>
                        <BoQBody>
                            <Itemlist>
@php $j = 1; @endphp
@foreach($erow['estimation_products'] as $row)
@php
$single_price = isset($quote_items[$row->id]->price) ? $quote_items[$row->id]->price : 0;
$item_total_price = isset($quote_items[$row->id]->total_price) ? $quote_items[$row->id]->total_price : 0;
@endphp
                                <Item RNoPart="{{ sprintf('%02d', $j) }}">
                                    <Provis>{{ $row->is_optional }}</Provis>
                                    <Qty>{{ $row->quantity }}</Qty>
                                    <QU>{{ $row->unit }}</QU>
                                    <UP>{{ $single_price }}</UP>
                                    <IT>{{ $item_total_price }}</IT>
@foreach($quotes_items_list[$row['id']] as $key => $qrow)
@php
    $single_price = '';
    $total_price = '';
    $sprice = '';
    $tprice = '';
    $sprice = export_money_format($qrow->price);
    $item_total = ($row->is_optional == 0) ? 0 : export_money_format($qrow->total_price);
    $tprice = export_money_format($item_total);
    $single_price = preg_replace('/[\x00-\x09\x0B-\x1F\x7F\xA0]/u', ' ', $sprice);
    $total_price = preg_replace('/[\x00-\x09\x0B-\x1F\x7F\xA0]/u', ' ', $tprice);
@endphp
                                    <QuotePrice RNoPart="{{ $key }}">
                                        <Text>{{ $qrow->quote->title }}</Text>
                                        <SinglePrice>{{ $single_price }}</SinglePrice>
                                        <TotalPrice>{{ $total_price }}</TotalPrice>
                                    </QuotePrice>
@endforeach
                                    <Description>
                                        <CompleteText>
                                            <DetailTxt>
                                                <Text>
                                                    <p>
                                                        <span>{{ $row->description }}</span>
                                                    </p>
                                                </Text>
                                            </DetailTxt>
                                            <OutlineText>
                                                <OutlTxt>
                                                    <TextOutlTxt>
                                                        <p>
                                                            <span>{{ $row->name }}</span>
                                                        </p>
                                                    </TextOutlTxt>
                                                </OutlTxt>
                                            </OutlineText>
                                        </CompleteText>
                                    </Description>
                                </Item>
@php $j++; @endphp
@endforeach
                            </Itemlist>
                            @include('taskly::project_estimations.export.gaeb_sub_groups', ['group' => $erow, 'site_money_format' => $site_money_format])
                        </BoQBody>
                    </BoQCtgy>
@php $i++; @endphp
@endforeach
@endif