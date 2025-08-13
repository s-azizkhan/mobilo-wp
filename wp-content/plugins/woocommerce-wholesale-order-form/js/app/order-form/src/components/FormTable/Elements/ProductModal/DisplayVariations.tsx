
import React from 'react';
import { Select } from 'antd';

const { Option } = Select;

const DisplayVariations = (props: any) => {

    const { orderFormData, itemProps, selectedVariation, setSelectedVariation, setVariationName } = props;
    const { orderFormId, product } = itemProps;

    if (product.type !== 'variable')
        return (<></>)

    const variations = orderFormData.formProducts[orderFormId]['variations'][product.id];

    if (variations.length <= 0)
        return (<></>);

    const variationsOptions = variations.map((variation: any) => {
        const name = variation.attributes.map((attributes: any) => {
            return <span key={attributes.id}>{attributes.name + ': ' + attributes.option}</span>
        });
        return <Option key={variation.id} value={variation.id}>{name}</Option>;
    });

    const onChange = (variationID: number) => {

        if (typeof variationID !== 'undefined') {

            const variation = variations.find((variation: any) => {
                return variation.id === variationID;
            });

            const name = variation.attributes.map((attributes: any) => {
                return attributes.name + ' : ' + attributes.option;
            });

            setSelectedVariation(variationID);
            setVariationName(name);

        } else {
            setSelectedVariation(0);
            setVariationName('');
        }

    }

    let selectProps = {}
    if (selectedVariation !== 0)
        selectProps = {
            value: selectedVariation
        }

    return (
        <Select
            showSearch
            placeholder='Select Variation'
            style={{ width: 250, marginTop: 10, marginBottom: 10 }}
            filterOption={false}
            notFoundContent='No results found'
            allowClear={true}
            {...selectProps}
            onChange={(variationId: any) => onChange(variationId)}
        >
            {variationsOptions}
        </Select>
    );

}

export default DisplayVariations;