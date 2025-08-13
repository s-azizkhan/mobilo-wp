
import React, { useState, useEffect } from 'react';
import { TreeSelect } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormDataActions } from '../../../store/actions';
const { setFormFilters } = orderFormDataActions;

const CategoryFilter = (props: any) => {

    const { orderForm, orderFormData, placeholder, styles, actions, orderFormId, fetchProducts, submitOnChange } = props;
    const { setFormFilters } = actions;
    const { categories } = orderForm;
    const selectedCategory = typeof orderFormData['formFilters'][orderFormId] === 'undefined' ? placeholder : orderFormData['formFilters'][orderFormId]['selectedCategory'];

    const [selectedCat, setSelectedCat] = useState(selectedCategory);

    const searchInput = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formFilters'][orderFormId]['searchInput'] !== 'undefined' ?
        orderFormData['formFilters'][orderFormId]['searchInput'] : '';

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

    if (categories !== undefined) {
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

    const onChange = (categoryName: string, treeData: Array<any>) => {

        try {

            let selected = categoryName ? categoryName : selectedCat;

            if (categoryName) {

                let iterate = (cat: any, data: any) => {

                    cat.children.forEach((cat2: any, index: number) => {

                        if (categoryName === cat2.name) {
                            selected = cat2.id;
                            return;
                        }

                        if (cat2.children.length > 0) {
                            iterate(cat2, data.children[index]);
                        }

                    });

                };

                categories.forEach((cat: any, index: number) => {
                    if (categoryName === cat.name) {
                        selected = cat.id;
                        return;
                    }

                    if (cat.children.length > 0)
                        iterate(cat, treeData[index]);
                });

                setSelectedCat(categoryName);
                setFormFilters({
                    [orderFormId]: {
                        ...orderFormData['formFilters'][orderFormId],
                        selectedCategory: selected
                    }
                })

            } else {
                setFormFilters({
                    [orderFormId]: {
                        ...orderFormData['formFilters'][orderFormId],
                        selectedCategory: ''
                    }
                })
            }

            if (submitOnChange) {
                fetchProducts({
                    search: searchInput,
                    category: selected,
                    active_page: 1,
                    searching: 'yes'
                });
            }

        } catch (error) {
            console.log(error)
        }

    }

    useEffect(() => {

        try {

            if (selectedCategory === '')
                setSelectedCat(placeholder);

        } catch (error) {
            console.log(error)
        }

    }, [selectedCategory]);

    return (
        <>
            <TreeSelect
                showSearch
                allowClear
                className='wwof-category-filter'
                value={selectedCat}
                treeData={treeData}
                placeholder={placeholder}
                treeDefaultExpandAll
                style={{
                    width: '100%',
                    ...styles
                }}
                onChange={(val: string) => onChange(val, treeData)}
            />
        </>
    );

}

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
    filter: store.filter
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setFormFilters
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFilter);
