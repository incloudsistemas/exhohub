<Listings>
    @foreach ($properties as $key => $property)
        <Listing>
            <ListingID>{{ $property->code }}</ListingID>
            <Title><![CDATA[{{ $property->title }}]]></Title>
            <TransactionType>{{ GetCanalProTransactionType(property: $property) }}</TransactionType>
            <PublicationType>{{ $property->publish_on_data['canal_pro']['publication_type'] }}</PublicationType>
            @if (!empty($property->url))
                <VirtualTourLink>{{ $property->url }}</VirtualTourLink>
            @endif
            <DetailViewUrl>{{ route('web.real-estate.properties.show', [$property->slug,  $property->code])}}</DetailViewUrl>
            <Media>
                @if (isset($property->embed_videos[0]))
                    <Item medium="video">https://www.youtube.com/watch?v={{ $property->embed_videos[0]['code'] }}</Item>
                @endif
                @foreach ($property->propertable->gallery_images as $image)
                    <Item medium="image" caption="{{ $image->name ?? $property->title }}">{{ CreateThumb(src: $image->getUrl(), width: 1280, height: 800, watermark: $property->has_watermark, watermarkPosition: $property->display_watermark_position) }}</Item>
                @endforeach
            </Media>
            <Details>
                <UsageType>{{ ucfirst(strtolower($property->usage->name)) }}</UsageType>
                <PropertyType>{{ GetCanalProPropertyType(property: $property) }}</PropertyType>
                <Description><![CDATA[{!! htmlentities($property->body, ENT_QUOTES, 'UTF-8') !!}]]></Description>
                @if ($property->propertable_type === 'real_estate_enterprises')
                    @if (!empty($property->propertable->min_price))
                        <ListPrice currency="BRL">{{ floor($property->propertable->min_price) }}</ListPrice>
                    @endif
                    @if (!empty($property->propertable->min_useful_area))
                        <LivingArea unit="square metres">{{ floor($property->propertable->min_useful_area) }}</LivingArea>
                    @endif
                    @if (!empty($property->propertable->min_total_area))
                        <LotArea unit="square metres">{{ floor($property->propertable->min_total_area) }}</LotArea>
                    @endif
                    @if (!empty($property->propertable->min_bedroom))
                        <Bedrooms>{{ $property->propertable->min_bedroom }}</Bedrooms>
                    @endif
                    @if (!empty($property->propertable->min_suite))
                        <Suites>{{ $property->propertable->min_suite }}</Suites>
                    @endif
                    @if (!empty($property->propertable->min_bathroom))
                        <Bathrooms>{{ $property->propertable->min_bathroom }}</Bathrooms>
                    @endif
                    @if (!empty($property->propertable->min_garage))
                        <Garage>{{ $property->propertable->min_garage }}</Garage>
                    @endif
                @else
                    @if (!empty($property->propertable->sale_price))
                        <ListPrice currency="BRL">{{ floor($property->propertable->sale_price) }}</ListPrice>
                    @endif
                    @if (!empty($property->propertable->rent_price))
                        <RentalPrice currency="BRL" period="{{ ucfirst(strtolower($property->propertable->rent_period->name)) }}">{{ floor($property->propertable->rent_price) }}</RentalPrice>
                        @if (isset($property->propertable->rent_warranties) && count($property->propertable->rent_warranties) > 0)
                            <Warranties>
                                @foreach ($property->propertable->rent_warranties as $warranty)
                                    <Warranty>{{ GetCanalProWarranty(label: $warranty) }}</Warranty>
                                @endforeach
                            </Warranties>
                        @endif
                    @endif
                    @if (!empty($property->propertable->useful_area))
                        <LivingArea unit="square metres">{{ floor($property->propertable->useful_area) }}</LivingArea>
                    @endif
                    @if (!empty($property->propertable->total_area))
                        <LotArea unit="square metres">{{ floor($property->propertable->total_area) }}</LotArea>
                    @endif
                    @if (!empty($property->propertable->bedroom))
                        <Bedrooms>{{ $property->propertable->bedroom }}</Bedrooms>
                    @endif
                    @if (!empty($property->propertable->suite))
                        <Suites>{{ $property->propertable->suite }}</Suites>
                    @endif
                    @if (!empty($property->propertable->bathroom))
                        <Bathrooms>{{ $property->propertable->bathroom }}</Bathrooms>
                    @endif
                    @if (!empty($property->propertable->garage))
                        <Garage>{{ $property->propertable->garage }}</Garage>
                    @endif
                @endif
                @if (!empty($property->condo_price))
                    <PropertyAdministrationFee currency="BRL">{{ floor($property->condo_price) }}</PropertyAdministrationFee>
                @endif
                @if (!empty($property->tax_price))
                    <YearlyTax currency="BRL">{{ floor($property->tax_price) }}</YearlyTax>
                @endif
                @if (!empty($property->floors))
                    <Floors>{{ $property->floors }}</Floors>
                @endif
                @if (!empty($property->units_per_floor))
                    <UnitFloor>{{ $property->units_per_floor }}</UnitFloor>
                @endif
                @if (!empty($property->towers))
                    <Buildings>{{ $property->towers }}</Buildings>
                @endif
                @if (!empty($property->construct_year))
                    <YearBuilt>{{ $property->construct_year }}</YearBuilt>
                @endif
                <Features>
                    @foreach (GetCanalProCharacteristics(property: $property) as $characteristic)
                        <Feature>{{ $characteristic }}</Feature>
                    @endforeach
                </Features>
            </Details>
            <Location displayAddress="{{ GetCanalProDisplayAddress(property: $property) }}">
                <Country abbreviation="BR">Brasil</Country>
                <State abbreviation="{{ $property->address->uf->name }}">{{ $property->address->display_uf }}</State>
                <City>{{ $property->address->city }}</City>
                @if (!empty($property->address->district))
                    <Neighborhood>{{ $property->address->district }}</Neighborhood>
                @endif
                @if (!empty($property->address->address_line))
                    <Address>{{ $property->address->address_line }}</Address>
                @endif
                @if (!empty($property->address->number))
                    <StreetNumber>{{ $property->address->number }}</StreetNumber>
                @endif
                @if (!empty($property->address->complement))
                    <Complement>{{ $property->address->complement }}</Complement>
                @endif
                <PostalCode>{{ $property->address->zipcode }}</PostalCode>
            </Location>
            <ContactInfo>
                <Name>{{ config('app.name') }} - {{ config('app.url') }}</Name>
                @if (isset($webSettings['mail'][0]) && !empty($webSettings['mail'][0]))
                    <Email>{{ $webSettings['mail'][0] }}</Email>
                @endif
                <Website>{{ config('app.url') }}</Website>
                <Logo>{{ asset('build/web/images/cover.jpg') }}</Logo>
                <OfficeName>{{ config('app.name') }}</OfficeName>
                @if (isset($webSettings['whatsapp'][0]) && !empty($webSettings['whatsapp'][0]['phone']))
                    <Telephone>{{ $webSettings['whatsapp'][0]['phone'] }}</Telephone>
                @elseif (isset($webSettings['phones'][0]) && !empty($webSettings['phones'][0]['phone']))
                    <Telephone>{{ $webSettings['phones'][0]['phone'] }}</Telephone>
                @endif
                @if (isset($webSettings['addresses'][0]))
                    <Location>
                        <Country abbreviation="BR">Brasil</Country>
                        @if (!empty($webSettings['addresses'][0]['uf']) && !empty($webSettings['addresses'][0]['state']))
                            <State abbreviation="{{ $webSettings['addresses'][0]['uf'] }}">{{ $webSettings['addresses'][0]['state'] }}</State>
                        @endif
                        @if (!empty($webSettings['addresses'][0]['city']))
                            <City>{{ $webSettings['addresses'][0]['city'] }}</City>
                        @endif
                        @if (!empty($webSettings['addresses'][0]['district']))
                            <Neighborhood>{{ $webSettings['addresses'][0]['district'] }}</Neighborhood>
                        @endif
                        @if (!empty($webSettings['addresses'][0]['address_line']))
                            <Address>{{ $webSettings['addresses'][0]['address_line'] }}</Address>
                        @endif
                        @if (!empty($webSettings['addresses'][0]['zipcode']))
                            <PostalCode>{{ $webSettings['addresses'][0]['zipcode'] }}</PostalCode>
                        @endif
                    </Location>
                @endif
            </ContactInfo>
        </Listing>
    @endforeach
</Listings>
