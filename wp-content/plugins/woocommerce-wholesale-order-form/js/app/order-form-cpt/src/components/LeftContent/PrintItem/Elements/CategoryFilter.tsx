
import React, { useEffect } from 'react';
import { TreeSelect } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { getPropValue } from '../../../../helpers/getPropValue';
import { updateStyling } from '../../../../helpers/updateStyling';

// Actions
import { stylingActions } from '../../../../store/actions';
import { findItemColumnId } from '../../../../helpers/findItemColumnId';

const { setStyles } = stylingActions;

const CategoryFilter = (props: any) => {

    const { products, placeholder, style, actions, dragAndDrop, styling } = props;
    const { categories } = products;
    const { setStyles } = actions;

    useEffect(() => {

        try {

            const columnId = findItemColumnId(dragAndDrop, 'category-filter');

            const check = getPropValue({ styling, id: columnId, target: 'props', style: 'submitOnChange', extra: '' });

            if (check === null) {
                updateStyling({
                    setStyles,
                    styling,
                    id: columnId,
                    target: 'props',
                    toUpdate: {
                        submitOnChange: true
                    }
                });
            }

        } catch (error) {
            console.log(error)
        }

    }, []);


    let treeData: any[] = [];
    let iterate = (cat: any, data: any) => {

        cat.children.forEach((cat2: any, index: number) => {

            data.children.push({
                'title': cat2.name,
                'value': cat2.name,
                'children': []
            });

            if (cat2.children.length > 0) {
                iterate(cat2, data.children[index]);
            }

        });

    };

    if (categories !== undefined && categories.length > 0) {
        categories.forEach((cat: any, index: number) => {
            treeData.push({
                'title': cat.name,
                'value': cat.name,
                'children': []
            });

            if (cat.children.length > 0)
                iterate(cat, treeData[index]);

        });
    }

    return (
        <>
            <TreeSelect
                showSearch
                allowClear
                className='wwof-category-filter'
                treeData={treeData}
                placeholder={placeholder}
                treeDefaultExpandAll
                style={{ ...style }}
            />
        </>
    );

}

const mapStateToProps = (store: any) => ({
    products: store.products,
    styling: store.styling,
    dragAndDrop: store.dragAndDrop
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setStyles
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFilter);
