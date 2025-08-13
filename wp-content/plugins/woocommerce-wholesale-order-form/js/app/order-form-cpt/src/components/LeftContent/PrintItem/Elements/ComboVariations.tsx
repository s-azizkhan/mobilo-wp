import React from 'react';
import { Select } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';


const { Option } = Select;

const ComboVariations = (props: any) => {

    const { products, product } = props;
    if (product.type !== 'variable')
        return (<></>);

    const variations = products.variations[product.id];

    if (typeof variations === 'undefined' || variations.length <= 0)
        return (<></>);

    const variationsOptions = variations.map((variation: any) => {
        const name = variation.attributes.map((attributes: any) => {
            return <span key={attributes.id}>{attributes.name + ': ' + attributes.option}</span>
        });
        return <Option key={variation.id} value={variation.id}>{name}</Option>;
    });

    const onChange = (variationID: number) => { }

    return (
        <Select
            showSearch
            placeholder='Select Variation'
            style={{ width: 250, marginTop: 10, textAlign: 'left' }}
            filterOption={false}
            notFoundContent='No results found'
            allowClear={true}
            onChange={(variationId: any) => onChange(variationId)}
        >
            {variationsOptions}
        </Select>
    );
}

const mapStateToProps = (store: any, props: any) => ({
    products: store.products
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ComboVariations);