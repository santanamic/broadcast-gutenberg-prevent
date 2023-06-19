import { addFilter } from "@wordpress/hooks";
import { createHigherOrderComponent } from "@wordpress/compose";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, ToggleControl } from "@wordpress/components";
import React, { useEffect } from "react";

(function () {
  const randomID = function () {
    const randString = Math.floor(Math.random() * 10000000).toString();

    return randString;
  };

  const withCustomOption = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
      const blockProps = useBlockProps();
      const { attributes, setAttributes } = props;
      const { idBroadcast, preventBroadcast } = attributes;

      const onChangePreventBroadcast = (value) => {
        setAttributes({ preventBroadcast: value });
      };

      useEffect(() => {
        if (idBroadcast == undefined || idBroadcast == "") {
          setAttributes({ idBroadcast: randomID() });
        }
      }, []);

      return (
        <>
          <InspectorControls>
            <PanelBody title="Broadcast" initialOpen={true}>
              <ToggleControl
                label="Não sincronizar"
                checked={preventBroadcast}
                onChange={onChangePreventBroadcast}
              />
            </PanelBody>
          </InspectorControls>
          <BlockEdit {...props} />
        </>
      );
    };
  }, "withCustomOption");

  const setBroadcastAttribute = (settings, name) => {
    return Object.assign({}, settings, {
      attributes: Object.assign({}, settings.attributes, {
        idBroadcast: { type: "string" },
        preventBroadcast: { type: "boolean" },
      }),
    });
  };

  addFilter(
    "editor.BlockEdit",
    "broadcast-gutenberg-prevent/prevent-block-broadcast",
    withCustomOption
  );
  addFilter(
    "blocks.registerBlockType",
    "broadcast-gutenberg-prevent/set-toolbar-button-attribute",
    setBroadcastAttribute
  );
})();
